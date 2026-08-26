<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\MembershipPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class MembershipController extends Controller
{
    public function index(Request $request)
    {
        $query = Membership::with(['member', 'package']);

        if ($memberId = $request->query('member_id')) {
            $query->where('member_id', $memberId);
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return $query->latest('start_date')->paginate((int) $request->query('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,member_id'],
            'package_id' => ['required', 'exists:membership_packages,package_id'],
            'start_date' => ['required', 'date'],
            'status' => ['nullable', 'in:active,frozen,expired,terminated'],
        ]);

        $package = MembershipPackage::findOrFail($data['package_id']);
        $start = Carbon::parse($data['start_date']);
        $data['end_date'] = match ($package->duration_type) {
            'days' => $start->copy()->addDays($package->duration_value),
            'weeks' => $start->copy()->addWeeks($package->duration_value),
            'months' => $start->copy()->addMonths($package->duration_value),
        };
        $data['status'] ??= 'active';

        return response()->json(Membership::create($data)->load(['member', 'package']), 201);
    }

    public function show(Membership $membership)
    {
        return $membership->load(['member', 'package', 'payments']);
    }

    public function update(Request $request, Membership $membership)
    {
        $data = $request->validate([
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date'],
            'freeze_start' => ['nullable', 'date'],
            'freeze_end' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:active,frozen,expired,terminated'],
        ]);

        $membership->update($data);

        return $membership->load(['member', 'package']);
    }

    public function destroy(Membership $membership)
    {
        $membership->delete();

        return response()->json(null, 204);
    }
}
