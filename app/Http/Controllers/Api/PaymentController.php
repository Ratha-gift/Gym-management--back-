<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Receipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['member', 'membership']);

        if ($memberId = $request->query('member_id')) {
            $query->where('member_id', $memberId);
        }

        return $query->latest('payment_date')->paginate((int) $request->query('per_page', 15));
    }

    /**
     * Record a payment, its line-item breakdown, and issue a receipt — all atomically.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,member_id'],
            'membership_id' => ['nullable', 'exists:memberships,membership_id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:Cash,Card,Bank Transfer,Other'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'details' => ['nullable', 'array'],
            'details.*.description' => ['required_with:details', 'string', 'max:255'],
            'details.*.amount' => ['required_with:details', 'numeric', 'min:0'],
        ]);

        $payment = DB::transaction(function () use ($data, $request) {
            $discount = $data['discount'] ?? 0;
            $netAmount = $data['amount'] - $discount;

            $payment = Payment::create([
                'member_id' => $data['member_id'],
                'membership_id' => $data['membership_id'] ?? null,
                'payment_date' => now(),
                'amount' => $data['amount'],
                'discount' => $discount,
                'net_amount' => $netAmount,
                'payment_method' => $data['payment_method'],
                'reference_no' => $data['reference_no'] ?? null,
                'status' => 'paid',
                'created_by' => $request->user()?->user_id,
            ]);

            foreach ($data['details'] ?? [] as $detail) {
                $payment->details()->create($detail);
            }

            Receipt::create([
                'payment_id' => $payment->payment_id,
                'receipt_no' => 'RCP' . str_pad((string) $payment->payment_id, 6, '0', STR_PAD_LEFT),
                'receipt_date' => now(),
                'amount' => $netAmount,
                'received_by' => $request->user()?->user_id,
            ]);

            return $payment;
        });

        return response()->json($payment->load(['details', 'receipt', 'member']), 201);
    }

    public function show(Payment $payment)
    {
        return $payment->load(['details', 'receipt', 'member', 'membership', 'createdBy']);
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'status' => ['sometimes', 'in:paid,partial,refunded'],
        ]);

        $payment->update($data);

        return $payment;
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json(null, 204);
    }
}
