<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Get all permissions assigned to a role.
     * POST /permission/get-permissions-by-role
     * Body: { role_id: number }
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        $role = Role::with('permissions')->findOrFail($request->role_id);

        return response()->json([
            'success' => true,
            'data'    => $role->permissions->map(fn($p) => [
                'id'         => $p->id,
                'name'       => $p->name,
                'guard_name' => $p->guard_name,
            ]),
        ]);
    }

    
    public function getPermissions(): JsonResponse
    {
        $roles = Permission::select('id', 'name', 'guard_name')
            ->orderBy('id')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $roles,
        ]);
    }


public function updateRolePermissions(Request $request): JsonResponse
{
    $request->validate([
        'role_id'            => 'required|integer|exists:roles,id',
        'permission_names'   => 'present|array',
        'permission_names.*' => 'string',  // ← remove exists check, we'll validate manually
    ]);

    $role = Role::findOrFail($request->role_id);

    // Get permissions with 'api' guard
    $permissions = Permission::where('guard_name', 'api')
        ->whereIn('name', $request->permission_names)
        ->pluck('name')
        ->toArray();

    // Sync with explicit guard
    $role->syncPermissions($permissions);

    return response()->json([
        'success' => true,
        'message' => 'Permissions updated successfully.',
        'data'    => $role->permissions->map(fn($p) => [
            'id'   => $p->id,
            'name' => $p->name,
        ]),
    ]);
}
}
