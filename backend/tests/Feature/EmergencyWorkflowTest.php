<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Emergency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class EmergencyWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_emergency_can_be_registered_without_triage_nurse()
    {
        $user = User::create([
            'name' => 'ER Nurse',
            'email' => 'ernurse@nishms.gov.ng',
            'password' => bcrypt('Password123#'),
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

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
        $user = User::create([
            'name' => 'ER Nurse 2',
            'email' => 'ernurse2@nishms.gov.ng',
            'password' => bcrypt('Password123#'),
            'status' => 'active',
        ]);

        Sanctum::actingAs($user);

        $this->postJson('/api/emergencies', [
            'triage_level' => 'purple', // invalid
            'chief_complaint' => 'Test',
            'mode_of_arrival' => 'walk-in',
        ])->assertStatus(422);
    }
}
