<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        return Permission::orderBy('module')->orderBy('permission_name')->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'permission_name' => ['required', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:50'],
        ]);

        return response()->json(Permission::create($data), 201);
    }

    public function show(Permission $permission)
    {
        return $permission;
    }

    public function update(Request $request, Permission $permission)
    {
        $data = $request->validate([
            'permission_name' => ['sometimes', 'required', 'string', 'max:100'],
            'module' => ['nullable', 'string', 'max:50'],
        ]);

        $permission->update($data);

        return $permission;
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();

        return response()->json(null, 204);
    }
}
