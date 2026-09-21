<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;

/**
 * Roles & access administration: view roles, inspect and edit the permissions
 * granted to each role.
 */
class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('display_name')->get();

        return response()->json([
            'roles' => $roles->map(fn ($r) => [
                'id' => $r->id,
                'name' => $r->name,
                'display_name' => $r->display_name,
                'description' => $r->description,
                'permissions_count' => $r->permissions_count,
                'users_count' => $r->users_count,
            ]),
        ]);
    }

    public function permissions()
    {
        $grouped = Permission::orderBy('module')->orderBy('display_name')->get()
            ->groupBy('module')
            ->map(fn ($group) => $group->map(fn ($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'display_name' => $p->display_name,
            ])->values());

        return response()->json(['modules' => $grouped]);
    }

    public function show($id)
    {
        $role = Role::with('permissions:id')->findOrFail($id);

        return response()->json([
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'display_name' => $role->display_name,
                'description' => $role->description,
            ],
            'permission_ids' => $role->permissions->pluck('id'),
        ]);
    }

    public function syncPermissions(Request $request, $id)
    {
        $validated = $request->validate([
            'permission_ids' => 'present|array',
            'permission_ids.*' => 'integer|exists:permissions,id',
        ]);

        $role = Role::findOrFail($id);

        // super_admin is an all-access role enforced in middleware; guard it so
        // an accidental save cannot strip the platform of its administrator.
        if ($role->name === 'super_admin') {
            return response()->json(['message' => 'The super_admin role has unrestricted access and cannot be edited here.'], 422);
        }

        $role->permissions()->sync($validated['permission_ids']);

        return response()->json([
            'message' => 'Permissions updated for ' . $role->display_name . '.',
            'permissions_count' => count($validated['permission_ids']),
        ]);
    }
}
