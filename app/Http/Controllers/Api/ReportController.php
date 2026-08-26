<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Payment;
use App\Models\Sale;

class ReportController extends Controller
{
    /**
     * Aggregate revenue and activity figures for the Reports page.
     * Computed with DB-level sums rather than paginated client-side
     * addition, so the numbers are correct regardless of page size.
     */
    public function summary()
    {
        $revenueByMethod = Payment::selectRaw('payment_method, SUM(net_amount) as total')
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method');

        return response()->json([
            'membership_revenue' => (float) Payment::sum('net_amount'),
            'sales_revenue' => (float) Sale::sum('net_amount'),
            'payments_count' => Payment::count(),
            'sales_count' => Sale::count(),
            'revenue_by_method' => $revenueByMethod,
            'memberships_by_status' => Membership::selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status'),
            'recent_payments' => Payment::with('member')->latest('payment_date')->limit(5)->get(),
            'recent_sales' => Sale::latest('sale_date')->limit(5)->get(),
        ]);
    }
}
