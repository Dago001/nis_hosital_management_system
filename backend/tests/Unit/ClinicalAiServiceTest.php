<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ClinicalAiService;

class ClinicalAiServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Force the offline knowledge-base path.
        config(['services.anthropic.api_key' => null]);
    }

    public function test_it_routes_malaria_queries_offline()
    {
        $result = (new ClinicalAiService())->respond('What is the treatment for severe malaria?');

        $this->assertEquals('offline', $result['source']);
        $this->assertStringContainsString('Malaria Treatment Protocol', $result['reply']);
    }

    public function test_it_routes_dosage_queries_offline()
    {
        $result = (new ClinicalAiService())->respond('paracetamol dosage for a 10kg child');

        $this->assertEquals('offline', $result['source']);
        $this->assertStringContainsString('Pediatric Dosing', $result['reply']);
    }

    public function test_it_returns_welcome_for_greetings()
    {
        $result = (new ClinicalAiService())->respond('hello');

        $this->assertEquals('offline', $result['source']);
        $this->assertStringContainsString('Clinical AI Companion', $result['reply']);
    }
}
