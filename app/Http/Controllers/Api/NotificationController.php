<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Real notifications derived from actual data, not a stored table — a
     * membership that's already past its end date but still marked
     * "active" (needs the status corrected / the member renewed), and one
     * about to expire in the next few days (a renewal reminder). Sorted
     * soonest-to-expire first so the most urgent one is always on top.
     */
    public function index(Request $request)
    {
        $withinDays = (int) $request->query('within_days', 3);

        $expired = Membership::with(['member', 'package'])
            ->where('status', 'active')
            ->where('end_date', '<', now())
            ->orderBy('end_date')
            ->get()
            ->map(fn ($m) => $this->toNotification($m, 'expired'));

        $expiringSoon = Membership::with(['member', 'package'])
            ->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($withinDays)])
            ->orderBy('end_date')
            ->get()
            ->map(fn ($m) => $this->toNotification($m, 'expiring_soon'));

        $notifications = $expired->concat($expiringSoon)->values();

        return response()->json([
            'total' => $notifications->count(),
            'notifications' => $notifications,
        ]);
    }

    private function toNotification(Membership $membership, string $type): array
    {
        return [
            'id' => "membership-{$membership->membership_id}-{$type}",
            'type' => $type,
            'member_id' => $membership->member_id,
            'member_name' => $membership->member?->name,
            'package_name' => $membership->package?->package_name,
            'end_date' => $membership->end_date,
        ];
    }
}
