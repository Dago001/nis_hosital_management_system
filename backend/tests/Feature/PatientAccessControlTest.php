<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class PatientAccessControlTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $roleName, array $permissionNames = []): User
    {
        $role = Role::create(['name' => $roleName, 'display_name' => ucfirst($roleName)]);
        foreach ($permissionNames as $pName) {
            $perm = Permission::firstOrCreate(['name' => $pName], ['display_name' => $pName, 'module' => 'clinical']);
            $role->permissions()->attach($perm->id);
        }
        $user = User::create([
            'name' => $roleName,
            'email' => $roleName . '@immigration.gov.ng',
            'password' => bcrypt('Password123#'),
            'status' => 'active',
        ]);
        $user->roles()->attach($role->id);

        return $user;
    }

    public function test_role_without_view_patients_cannot_read_patient_records()
    {
        // store_officer has no clinical need-to-know.
        Sanctum::actingAs($this->userWithRole('store_officer', ['view_inventory']));

        $this->getJson('/api/patients?search=NIS')->assertStatus(403);
        $this->getJson('/api/patients/1')->assertStatus(403);
    }

    public function test_role_with_view_patients_can_read_patient_records()
    {
        Sanctum::actingAs($this->userWithRole('doctor', ['view_patients']));

        // Authorized: 200 (empty search result set is fine).
        $this->getJson('/api/patients?search=NIS')->assertStatus(200);
    }

    public function test_unauthenticated_cannot_read_patient_records()
    {
        $this->getJson('/api/patients?search=NIS')->assertStatus(401);
    }
}
