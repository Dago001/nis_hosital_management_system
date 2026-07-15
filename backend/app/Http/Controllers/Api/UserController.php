<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Staff;
use App\Models\Department;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'staff.department']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->paginate(15);

        return response()->json([
            'users' => UserResource::collection($users),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage()
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::defaults()],
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            
            // Optional Staff parameters
            'is_staff' => 'required|boolean',
            'first_name' => 'nullable|required_if:is_staff,true|string|max:255',
            'last_name' => 'nullable|required_if:is_staff,true|string|max:255',
            'department_id' => 'nullable|required_if:is_staff,true|exists:departments,id',
            'service_number' => 'nullable|string|unique:staff,service_number',
            'rank' => 'nullable|string',
            'phone' => 'nullable|required_if:is_staff,true|string|max:20',
            'specialization' => 'nullable|string',
        ]);

        $user = DB::transaction(function () use ($validated) {
            // 1. Create the user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'status' => 'active'
            ]);

            // 2. Attach roles
            foreach ($validated['roles'] as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->roles()->attach($role->id);
                }
            }

            // 3. Create staff details if marked as staff
            if ($validated['is_staff']) {
                Staff::create([
                    'user_id' => $user->id,
                    'department_id' => $validated['department_id'],
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'service_number' => $validated['service_number'] ?? null,
                    'rank' => $validated['rank'] ?? null,
                    'phone' => $validated['phone'],
                    'specialization' => $validated['specialization'] ?? null,
                    'status' => 'active'
                ]);
            }

            return $user;
        });

        return response()->json([
            'message' => 'User created successfully.',
            'user' => new UserResource($user->load(['roles', 'staff.department']))
        ], 210);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
            
            // Optional Staff parameters
            'is_staff' => 'required|boolean',
            'first_name' => 'nullable|required_if:is_staff,true|string|max:255',
            'last_name' => 'nullable|required_if:is_staff,true|string|max:255',
            'department_id' => 'nullable|required_if:is_staff,true|exists:departments,id',
            'service_number' => 'nullable|string|unique:staff,service_number,' . ($user->staff ? $user->staff->id : 'NULL'),
            'rank' => 'nullable|string',
            'phone' => 'nullable|required_if:is_staff,true|string|max:20',
            'specialization' => 'nullable|string',
        ]);

        $user = DB::transaction(function () use ($validated, $user) {
            // 1. Update user
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            // 2. Sync roles
            $roleIds = Role::whereIn('name', $validated['roles'])->pluck('id');
            $user->roles()->sync($roleIds);

            // 3. Update or create staff
            if ($validated['is_staff']) {
                $user->staff()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'department_id' => $validated['department_id'],
                        'first_name' => $validated['first_name'],
                        'last_name' => $validated['last_name'],
                        'service_number' => $validated['service_number'] ?? null,
                        'rank' => $validated['rank'] ?? null,
                        'phone' => $validated['phone'],
                        'specialization' => $validated['specialization'] ?? null,
                        'status' => 'active'
                    ]
                );
            } else {
                if ($user->staff) {
                    $user->staff()->delete();
                }
            }

            return $user;
        });

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => new UserResource($user->load(['roles', 'staff.department']))
        ]);
    }

    public function toggleStatus(int $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        // Toggle status: active <-> suspended
        $user->status = ($user->status === 'active') ? 'suspended' : 'active';
        $user->save();

        return response()->json([
            'message' => "User account has been " . ($user->status === 'active' ? 'activated' : 'deactivated') . ".",
            'user' => new UserResource($user->load(['roles', 'staff.department']))
        ]);
    }

    public function resetPassword(Request $request, int $id)
    {
        $request->validate([
            'password' => ['required', 'string', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ]);

        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'User password reset successfully.'
        ]);
    }

    public function getSetupData()
    {
        $roles = Role::all();
        $departments = Department::all();

        return response()->json([
            'roles' => $roles,
            'departments' => $departments
        ]);
    }
}
