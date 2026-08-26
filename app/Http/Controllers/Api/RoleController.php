<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return Role::withCount('users')->orderBy('role_name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role_name' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        return response()->json(Role::create($data), 201);
    }

    public function show(Role $role)
    {
        return $role->load('permissions');
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'role_name' => ['sometimes', 'required', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role->update($data);

        return $role;
    }

    public function destroy(Role $role)
    {
        $role->delete();

        return response()->json(null, 204);
    }

    /** Replace this role's full permission set in one shot (checkbox-matrix save). */
    public function syncPermissions(Request $request, Role $role)
    {
        $data = $request->validate([
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,permission_id'],
        ]);

        $role->permissions()->sync($data['permission_ids'] ?? []);

        return $role->load('permissions');
    }
}
