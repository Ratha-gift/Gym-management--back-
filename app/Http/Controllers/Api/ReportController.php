<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Sale;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    /**
     * Aggregate revenue and activity figures for the Reports page, optionally
     * scoped to a date range via `start_date`/`end_date` (both inclusive,
     * YYYY-MM-DD) — the period presets on the frontend (Today/This Week/This
     * Month/This Year/Custom) all resolve to this same pair of params, with
     * neither sent at all meaning "all time".
     *
     * Computed with DB-level sums rather than paginated client-side addition,
     * so the numbers are correct regardless of page size. Membership status
     * counts are a current snapshot, not scoped to the date range — a
     * membership's status is "now", not a historical event like a payment.
     */
    public function summary(Request $request)
    {
        $start = $request->query('start_date');
        $end = $request->query('end_date');

        $paymentQuery = Payment::query();
        $saleQuery = Sale::query();

        if ($start) {
            $paymentQuery->whereDate('payment_date', '>=', $start);
            $saleQuery->whereDate('sale_date', '>=', $start);
        }
        if ($end) {
            $paymentQuery->whereDate('payment_date', '<=', $end);
            $saleQuery->whereDate('sale_date', '<=', $end);
        }

        $revenueByMethod = (clone $paymentQuery)
            ->selectRaw('payment_method, SUM(net_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        return response()->json([
            'membership_revenue' => (float) (clone $paymentQuery)->sum('net_amount'),
            'sales_revenue' => (float) (clone $saleQuery)->sum('net_amount'),
            'payments_count' => (clone $paymentQuery)->count(),
            'sales_count' => (clone $saleQuery)->count(),
            'revenue_by_method' => $revenueByMethod,
            'memberships_by_status' => Membership::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'recent_payments' => (clone $paymentQuery)->with('member')->latest('payment_date')->limit(5)->get(),
            'recent_sales' => (clone $saleQuery)->latest('sale_date')->limit(5)->get(),
        ]);
    }
}
