<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\MembershipPackage;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats()
    {
        return response()->json([
            'total_members' => Member::count(),
            'active_members' => Member::whereHas('memberships', fn ($q) => $q->where('status', 'active'))->count(),
            'expired_members' => Member::whereHas('memberships', fn ($q) => $q->where('status', 'expired'))->count(),
            'packages' => MembershipPackage::count(),
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
