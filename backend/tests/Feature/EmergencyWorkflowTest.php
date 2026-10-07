<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Emergency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class EmergencyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function nurse(string $email): User
    {
        $user = User::create([
            'name' => 'ER Nurse',
            'email' => $email,
            'password' => bcrypt('Password123#'),
            'status' => 'active',
        ]);
        $role = Role::create(['name' => 'nurse', 'display_name' => 'Nurse']);
        $user->roles()->attach($role->id);

        return $user;
    }

    public function test_emergency_can_be_registered_without_triage_nurse()
    {
        Sanctum::actingAs($this->nurse('ernurse@immigration.gov.ng'));

        // Regression: registering without 'triaged_by' must not raise
        // "Undefined array key" — it should succeed with triaged_at = null.
        $response = $this->postJson('/api/emergencies', [
            'triage_level' => 'yellow',
            'chief_complaint' => 'Road traffic accident, minor lacerations',
            'mode_of_arrival' => 'walk-in',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('emergencies', [
            'chief_complaint' => 'Road traffic accident, minor lacerations',
            'triage_level' => 'yellow',
            'status' => 'waiting',
            'triaged_at' => null,
        ]);
    }

    public function test_emergency_registration_validates_triage_level()
    {
        Sanctum::actingAs($this->nurse('ernurse2@immigration.gov.ng'));

        $this->postJson('/api/emergencies', [
            'triage_level' => 'purple', // invalid
            'chief_complaint' => 'Test',
            'mode_of_arrival' => 'walk-in',
        ])->assertStatus(422);
    }

    public function test_emergency_registration_blocked_for_unauthorized_role()
    {
        $user = User::create([
            'name' => 'Store Officer',
            'email' => 'store@immigration.gov.ng',
            'password' => bcrypt('Password123#'),
            'status' => 'active',
        ]);
        $role = Role::create(['name' => 'store_officer', 'display_name' => 'Store Officer']);
        $user->roles()->attach($role->id);

        Sanctum::actingAs($user);

        // A non-clinical role must not be able to register emergencies.
        $this->postJson('/api/emergencies', [
            'triage_level' => 'yellow',
            'chief_complaint' => 'RTA',
            'mode_of_arrival' => 'walk-in',
        ])->assertStatus(403);
    }
}
