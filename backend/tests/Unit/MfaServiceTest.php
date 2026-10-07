<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class MfaServiceTest extends TestCase
{
    public function test_it_generates_six_digit_otp_and_stores_hashed_in_cache()
    {
        Mail::fake();

        $service = new MfaService();
        $user = new User();
        $user->id = 99;
        $user->email = 'test@immigration.gov.ng';

        $otp = $service->generateOtp($user);

        $this->assertEquals(6, strlen($otp));
        $this->assertTrue(is_numeric($otp));

        $key = 'mfa_otp_99';
        $cached = Cache::get($key);

        // The plaintext code must NOT be stored — only a verifiable hash.
        $this->assertNotEquals($otp, $cached);
        $this->assertTrue(Hash::check($otp, $cached));
    }

    public function test_it_verifies_otp_successfully()
    {
        $service = new MfaService();
        $user = new User();
        $user->id = 99;

        // Seed cache with the hashed code, as the service now stores it.
        $key = 'mfa_otp_99';
        Cache::put($key, Hash::make('123456'), 5);

        $isVerified = $service->verifyOtp($user, '123456');

        $this->assertTrue($isVerified);

        // Verify it is flushed from cache after use
        $this->assertNull(Cache::get($key));
    }

    public function test_it_fails_verification_with_invalid_otp()
    {
        $service = new MfaService();
        $user = new User();
        $user->id = 99;

        // Seed cache with the hashed code.
        $key = 'mfa_otp_99';
        Cache::put($key, Hash::make('123456'), 5);

        $isVerified = $service->verifyOtp($user, '654321');
        $this->assertFalse($isVerified);
    }
}
