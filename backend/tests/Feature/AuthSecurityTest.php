<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Rate limiters persist across requests; start each test from a clean slate.
        RateLimiter::clear('login:agent@immigration.gov.ng|127.0.0.1');
    }

    private function makeUser(string $status = 'active'): User
    {
        return User::create([
            'name' => 'Agent',
            'email' => 'agent@immigration.gov.ng',
            'password' => bcrypt('Password123#'),
            'status' => $status,
        ]);
    }

    public function test_valid_credentials_return_a_bearer_token(): void
    {
        $this->makeUser();

        $this->postJson('/api/login', [
            'email' => 'agent@immigration.gov.ng',
            'password' => 'Password123#',
        ])->assertStatus(200)
          ->assertJsonStructure(['access_token', 'token_type', 'user']);
    }

    public function test_wrong_password_is_rejected_with_422(): void
    {
        $this->makeUser();

        $this->postJson('/api/login', [
            'email' => 'agent@immigration.gov.ng',
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    public function test_unknown_and_wrong_password_return_identical_responses(): void
    {
        $this->makeUser();

        $wrongPassword = $this->postJson('/api/login', [
            'email' => 'agent@immigration.gov.ng',
            'password' => 'wrong-password',
        ]);

        $unknownUser = $this->postJson('/api/login', [
            'email' => 'ghost@immigration.gov.ng',
            'password' => 'wrong-password',
        ]);

        // No user enumeration: both must yield the same status and message.
        $this->assertSame($wrongPassword->status(), $unknownUser->status());
        $this->assertSame(
            $wrongPassword->json('message'),
            $unknownUser->json('message')
        );
    }

    public function test_repeated_failures_trigger_a_lockout(): void
    {
        $this->makeUser();

        // The 'login' limiter allows 5 attempts per minute per email+IP.
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', [
                'email' => 'agent@immigration.gov.ng',
                'password' => 'wrong-password',
            ]);
        }

        // The next attempt must be locked out (429), even with the correct password.
        $this->postJson('/api/login', [
            'email' => 'agent@immigration.gov.ng',
            'password' => 'Password123#',
        ])->assertStatus(429);
    }

    public function test_suspended_account_cannot_log_in(): void
    {
        $this->makeUser(status: 'suspended');

        $this->postJson('/api/login', [
            'email' => 'agent@immigration.gov.ng',
            'password' => 'Password123#',
        ])->assertStatus(403);
    }
}
