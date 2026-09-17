<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemberController extends Controller
{
    public function index(Request $request)
    {
        $query = Member::with(['latestMembership', 'activeMembership.package', 'openAttendance'])->latest('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        return $query->paginate((int) $request->query('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        $data['member_code'] = $this->nextMemberCode();
        $data['qr_code'] = (string) Str::uuid();
        $data['status'] ??= 'active';

        return response()->json(Member::create($data), 201);
    }

    public function show(Member $member)
    {
        return $member->load(['memberships.package', 'payments', 'attendances', 'openAttendance']);
    }

    public function update(Request $request, Member $member)
    {
        $data = $request->validate([
            'first_name' => ['sometimes', 'required', 'string', 'max:50'],
            'last_name' => ['sometimes', 'required', 'string', 'max:50'],
            'gender' => ['nullable', 'in:Male,Female,Other'],
            'date_of_birth' => ['nullable', 'date'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:100'],
            'address' => ['nullable', 'string'],
            'photo' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $member->update($data);

        return $member;
    }

    public function destroy(Member $member)
    {
        $member->delete();

        return response()->json(null, 204);
    }

    private function nextMemberCode(): string
    {
        $last = Member::orderByDesc('member_id')->value('member_id') ?? 0;

        return 'MEM' . str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
    }
}
