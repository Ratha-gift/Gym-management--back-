<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        return Sale::with('items.product')->latest('sale_date')->paginate((int) $request->query('per_page', 15));
    }

    /**
     * Ring up a sale: creates the sale, its line items, decrements stock, and
     * computes change — all atomically.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'discount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:Cash,Card,Bank Transfer,Other'],
            'received_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,product_id'],
            'items.*.qty' => ['required', 'integer', 'min:1'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $sale = DB::transaction(function () use ($data, $request) {
            $products = Product::whereIn('product_id', collect($data['items'])->pluck('product_id'))
                ->lockForUpdate()
                ->get()
                ->keyBy('product_id');

            $totalAmount = 0;
            $lineItems = [];

            foreach ($data['items'] as $item) {
                $product = $products[$item['product_id']];

                if ($product->stock_qty < $item['qty']) {
                    throw ValidationException::withMessages([
                        'items' => ["Not enough stock for {$product->product_name}."],
                    ]);
                }

                $lineDiscount = $item['discount'] ?? 0;
                $lineTotal = ($product->unit_price * $item['qty']) - $lineDiscount;
                $totalAmount += $product->unit_price * $item['qty'];

                $lineItems[] = [
                    'product_id' => $product->product_id,
                    'qty' => $item['qty'],
                    'unit_price' => $product->unit_price,
                    'discount' => $lineDiscount,
                    'total' => $lineTotal,
                ];

                $product->decrement('stock_qty', $item['qty']);
            }

            $discount = $data['discount'] ?? 0;
            $netAmount = $totalAmount - $discount;

            if ($data['received_amount'] < $netAmount) {
                throw ValidationException::withMessages([
                    'received_amount' => ['Received amount is less than the total due.'],
                ]);
            }

            $sale = Sale::create([
                'sale_no' => 'SALE' . now()->format('ymd') . '-' . str_pad((string) (Sale::max('sale_id') + 1), 4, '0', STR_PAD_LEFT),
                'sale_date' => now(),
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'net_amount' => $netAmount,
                'payment_method' => $data['payment_method'],
                'received_amount' => $data['received_amount'],
                'change_amount' => $data['received_amount'] - $netAmount,
                'created_by' => $request->user()?->user_id,
            ]);

            $sale->items()->createMany($lineItems);

            return $sale;
        });

        return response()->json($sale->load('items.product'), 201);
    }

    public function show(Sale $sale)
    {
        return $sale->load(['items.product', 'createdBy']);
    }

    public function update(Request $request, Sale $sale)
    {
        // Sales are immutable once recorded; nothing to update via this endpoint.
        return response()->json($sale->load('items.product'));
    }

    public function destroy(Sale $sale)
    {
        $sale->delete();

        return response()->json(null, 204);
    }
}
