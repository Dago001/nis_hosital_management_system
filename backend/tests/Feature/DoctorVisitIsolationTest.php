<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Patient;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Verifies per-doctor isolation: a doctor may only open/complete a consult for
 * a patient assigned to them, senior clinical roles get break-glass access, and
 * completing a consult never silently reassigns another doctor's patient.
 */
class DoctorVisitIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function makeDoctor(string $key, string $roleName = 'doctor', array $perms = ['consult_patients']): array
    {
        $role = Role::firstOrCreate(['name' => $roleName], ['display_name' => ucfirst($roleName)]);
        foreach ($perms as $p) {
            $perm = Permission::firstOrCreate(['name' => $p], ['display_name' => $p, 'module' => 'clinical']);
            $role->permissions()->syncWithoutDetaching([$perm->id]);
        }
        $user = User::create([
            'name' => $key,
            'email' => $key . '@immigration.gov.ng',
            'password' => bcrypt('Password123#'),
            'status' => 'active',
        ]);
        $user->roles()->attach($role->id);
        $staff = Staff::create([
            'user_id' => $user->id,
            'first_name' => ucfirst($key),
            'last_name' => 'Doctor',
            'phone' => '0800000000',
            'department_id' => $this->dept->id,
            'status' => 'active',
            'service_number' => 'NIS/DOC/' . strtoupper($key),
        ]);

        return [$user, $staff];
    }

    private Department $dept;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->dept = Department::create(['name' => 'General OPD', 'code' => 'GOPD']);
        $this->patient = Patient::create([
            'first_name' => 'Test', 'last_name' => 'Patient', 'gender' => 'Male',
            'marital_status' => 'Single', 'date_of_birth' => '1990-01-01',
            'phone' => '08010001000', 'address' => 'Abuja',
            'immigration_service_number' => 'NIS/PAT/999001',
        ]);
    }

    private function waitingVisitFor(Staff $doctor): Visit
    {
        return Visit::create([
            'patient_id' => $this->patient->id,
            'staff_id' => $doctor->id,
            'department_id' => $this->dept->id,
            'vitals_blood_pressure' => '120/80',
        ]);
    }

    private function consultBody(): array
    {
        return [
            'chief_complaint' => 'Headache',
            'soap_notes_subjective' => 's', 'soap_notes_objective' => 'o',
            'soap_notes_assessment' => 'a', 'soap_notes_plan' => 'p',
            'diagnosis_icd10' => 'R51', 'diagnosis_description' => 'Headache',
        ];
    }

    public function test_other_doctor_cannot_open_or_complete_assigned_visit()
    {
        [, $doc1] = $this->makeDoctor('doc1');
        [$doc2User] = $this->makeDoctor('doc2');
        $visit = $this->waitingVisitFor($doc1);

        Sanctum::actingAs($doc2User);

        $this->getJson("/api/clinical/active-visit/{$this->patient->id}")->assertStatus(403);
        $this->postJson("/api/clinical/consult/{$visit->id}", $this->consultBody())->assertStatus(403);

        // The visit must remain assigned to the original doctor.
        $this->assertSame($doc1->id, $visit->fresh()->staff_id);
    }

    public function test_assigned_doctor_can_open_and_complete_without_reassignment()
    {
        [$doc1User, $doc1] = $this->makeDoctor('doc1');
        $visit = $this->waitingVisitFor($doc1);

        Sanctum::actingAs($doc1User);

        $this->getJson("/api/clinical/active-visit/{$this->patient->id}")->assertStatus(200);
        $this->postJson("/api/clinical/consult/{$visit->id}", $this->consultBody())->assertStatus(200);

        // staff_id stays with the assigned doctor.
        $this->assertSame($doc1->id, $visit->fresh()->staff_id);
    }

    public function test_medical_director_has_break_glass_access()
    {
        [, $doc1] = $this->makeDoctor('doc1');
        // medical_director without consult_patients permission — admitted via role.
        [$dirUser] = $this->makeDoctor('director', 'medical_director', []);
        $visit = $this->waitingVisitFor($doc1);

        Sanctum::actingAs($dirUser);

        $this->getJson("/api/clinical/active-visit/{$this->patient->id}")->assertStatus(200);
    }
}
