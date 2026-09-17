<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipPackage;
use App\Models\Payment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        $totalMembers = Member::count();

        // Members created before the last 30 days, so "new this month" and
        // its growth badge are both derived from real signup dates rather
        // than a fabricated trend number.
        $membersOver30DaysOld = Member::where('created_at', '<=', now()->subDays(30))->count();
        $newMembersThisMonth = Member::where('created_at', '>', now()->subDays(30))->count();

        $monthlySignups = [];
        $currentMonthStart = now()->startOfMonth();
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $currentMonthStart->copy()->subMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();
            $monthlySignups[] = [
                'month' => $monthStart->format('M'),
                'count' => Member::whereBetween('created_at', [$monthStart, $monthEnd])->count(),
            ];
        }

        // Revenue figures, mirroring the same DB-aggregation approach as
        // ReportController::summary() — this-month is scoped the same way
        // the Reports page's own "This Month" preset resolves it.
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();
        $paymentsThisMonthQuery = Payment::whereBetween('payment_date', [$monthStart, $monthEnd]);
        $latestMember = Member::with('latestMembership')->latest('created_at')->first();
        $quickStart = [
            'member' => (bool) $latestMember,
            'package' => (bool) $latestMember?->latestMembership,
            'payment' => $latestMember ? $latestMember->payments()->exists() : false,
            'attendance' => $latestMember ? $latestMember->attendances()->exists() : false,
        ];

        return response()->json([
            'quick_start' => $quickStart,
            'total_members' => $totalMembers,
            'total_revenue' => (float) Payment::sum('net_amount'),
            'revenue_this_month' => (float) (clone $paymentsThisMonthQuery)->sum('net_amount'),
            'payments_this_month' => (clone $paymentsThisMonthQuery)->count(),
            
            'payments_count' => Payment::count(),
            'active_members' => Member::whereHas('memberships', fn ($q) => $q->where('status', 'active'))->count(),
            'frozen_members' => Member::whereHas('memberships', fn ($q) => $q->where('status', 'frozen'))->count(),
            'expired_members' => Member::whereHas('memberships', fn ($q) => $q->where('status', 'expired'))->count(),
            'terminated_members' => Member::whereHas('memberships', fn ($q) => $q->where('status', 'terminated'))->count(),
            'packages' => MembershipPackage::count(),
            'new_members_this_month' => $newMembersThisMonth,
            'members_before_this_month' => $membersOver30DaysOld,
            'monthly_signups' => $monthlySignups,
        ]);
    }

    public function recentMembers(Request $request)
    {
        $limit = min((int) $request->query('limit', 5), 50);

        return response()->json(
            Member::with(['latestMembership', 'openAttendance'])->orderByDesc('created_at')->limit($limit)->get()
        );
    }
}
