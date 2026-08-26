<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = Attendance::with('member');

        if ($memberId = $request->query('member_id')) {
            $query->where('member_id', $memberId);
        }

        if ($request->boolean('open')) {
            $query->whereNull('check_out');
        }

        if ($date = $request->query('date')) {
            $query->whereDate('check_in', $date);
        }

        if ($search = $request->query('search')) {
            $query->whereHas('member', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('member_code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->latest('check_in')->paginate((int) $request->query('per_page', 15));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'member_id' => ['required', 'exists:members,member_id'],
            'method' => ['nullable', 'in:QR,Manual'],
        ]);

        $alreadyIn = Attendance::where('member_id', $data['member_id'])->whereNull('check_out')->exists();
        if ($alreadyIn) {
            return response()->json(['message' => 'This member is already checked in.'], 422);
        }

        $attendance = Attendance::create([
            'member_id' => $data['member_id'],
            'check_in' => now(),
            'method' => $data['method'] ?? 'QR',
            'status' => 'Present',
            'created_by' => $request->user()?->user_id,
        ]);

        return response()->json($attendance->load('member'), 201);
    }

    public function show(Attendance $attendance)
    {
        return $attendance->load('member');
    }

    public function update(Request $request, Attendance $attendance)
    {
        $data = $request->validate([
            'check_out' => ['nullable', 'date'],
            'status' => ['sometimes', 'in:Present,Absent'],
        ]);

        $attendance->update($data);

        return $attendance;
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();

        return response()->json(null, 204);
    }
}
