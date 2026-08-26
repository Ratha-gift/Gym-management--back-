<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MembershipPackage;
use Illuminate\Http\Request;

class MembershipPackageController extends Controller
{
    public function index()
    {
        return MembershipPackage::orderBy('price')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'package_name' => ['required', 'string', 'max:100'],
            'duration_type' => ['required', 'in:days,weeks,months'],
            'duration_value' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'in:active,inactive'],
        ]);

        return response()->json(MembershipPackage::create($data), 201);
    }

    public function show(MembershipPackage $membershipPackage)
    {
        return $membershipPackage;
    }

    public function update(Request $request, MembershipPackage $membershipPackage)
    {
        $data = $request->validate([
            'package_name' => ['sometimes', 'required', 'string', 'max:100'],
            'duration_type' => ['sometimes', 'required', 'in:days,weeks,months'],
            'duration_value' => ['sometimes', 'required', 'integer', 'min:1'],
            'price' => ['sometimes', 'required', 'numeric', 'min:0'],
            'description' => ['nullable', 'string'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $membershipPackage->update($data);

        return $membershipPackage;
    }

    public function destroy(MembershipPackage $membershipPackage)
    {
        $membershipPackage->delete();

        return response()->json(null, 204);
    }
}
