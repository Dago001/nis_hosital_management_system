<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Patient;
use App\Services\DrugSafetyService;

class DrugSafetyServiceTest extends TestCase
{
    private function patient(?string $allergies): Patient
    {
        $p = new Patient();
        $p->allergies = $allergies;
        return $p;
    }

    public function test_it_flags_cross_reactive_allergy()
    {
        $svc = new DrugSafetyService();
        $res = $svc->check($this->patient('Penicillin, Dust'), ['Amoxicillin 500mg']);

        $this->assertTrue($res['has_alerts']);
        $this->assertNotEmpty($res['allergy_alerts']);
    }

    public function test_it_flags_known_interaction()
    {
        $svc = new DrugSafetyService();
        $res = $svc->check($this->patient('None'), ['Warfarin', 'Aspirin 75mg']);

        $this->assertNotEmpty($res['interaction_alerts']);
        $this->assertEquals('high', $res['interaction_alerts'][0]['severity']);
    }

    public function test_it_returns_no_alerts_for_safe_prescription()
    {
        $svc = new DrugSafetyService();
        $res = $svc->check($this->patient('None'), ['Paracetamol 500mg']);

        $this->assertFalse($res['has_alerts']);
    }
}
