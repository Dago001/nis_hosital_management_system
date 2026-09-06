<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChatbotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.anthropic.api_key' => null]); // force offline knowledge base
    }

    private function chat(string $message, array $state = [])
    {
        return $this->postJson('/api/chatbot', ['message' => $message, 'state' => $state]);
    }

    public function test_it_answers_greetings()
    {
        $this->chat('hello')
            ->assertOk()
            ->assertJsonPath('booked', false)
            ->assertJsonFragment(['source' => 'offline']);
    }

    public function test_new_patient_booking_flow_creates_request()
    {
        $state = $this->chat('I want to book an appointment')->json('state');
        $this->assertEquals('booking', $state['flow']);

        $state = $this->chat('new', $state)->json('state');
        $state = $this->chat('Grace', $state)->json('state');
        $state = $this->chat('Okafor', $state)->json('state');
        $state = $this->chat('grace@example.com', $state)->json('state');
        $state = $this->chat('08011122233', $state)->json('state');
        $state = $this->chat('2030-01-15', $state)->json('state');
        $state = $this->chat('09:30', $state)->json('state');
        $final = $this->chat('Persistent headache', $state);

        $final->assertOk()->assertJsonPath('booked', true);
        $this->assertDatabaseHas('appointment_requests', [
            'first_name' => 'Grace',
            'last_name' => 'Okafor',
            'appointment_date' => '2030-01-15',
            'appointment_time' => '09:30',
            'status' => 'pending',
        ]);
    }

    public function test_returning_patient_booking_uses_hospital_number()
    {
        $patient = Patient::create([
            'first_name' => 'Musa', 'last_name' => 'Bello', 'gender' => 'Male',
            'date_of_birth' => '1980-01-01', 'phone' => '08099998888', 'address' => 'Abuja',
            'immigration_service_number' => 'NIS/PAT/999001',
        ]);

        $state = $this->chat('book appointment')->json('state');
        $reply = $this->chat('NIS/PAT/999001', $state);
        $reply->assertOk();
        $this->assertStringContainsString('Welcome back', $reply->json('reply'));
        $state = $reply->json('state');

        $state = $this->chat('2030-02-20', $state)->json('state');
        $state = $this->chat('10:00', $state)->json('state');
        $final = $this->chat('skip', $state);

        $final->assertJsonPath('booked', true);
        $this->assertDatabaseHas('appointment_requests', [
            'immigration_service_number' => 'NIS/PAT/999001',
            'first_name' => 'Musa',
            'appointment_date' => '2030-02-20',
        ]);
    }

    public function test_unknown_hospital_number_is_rejected()
    {
        $state = $this->chat('book appointment')->json('state');
        $reply = $this->chat('NIS/PAT/000000', $state);
        $this->assertStringContainsString('could not find', strtolower($reply->json('reply')));
        $this->assertEquals('hospital_number', $reply->json('state')['step']);
    }
}
