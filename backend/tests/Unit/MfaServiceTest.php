<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Services\MfaService;
use Illuminate\Support\Facades\Cache;

class MfaServiceTest extends TestCase
{
    public function test_it_generates_six_digit_otp_and_stores_in_cache()
    {
        $service = new MfaService();
        $user = new User();
        $user->id = 99;

        $otp = $service->generateOtp($user);

        $this->assertEquals(6, strlen($otp));
        $this->assertTrue(is_numeric($otp));
        
        $key = 'mfa_otp_99';
        $this->assertEquals($otp, Cache::get($key));
    }

    public function test_it_verifies_otp_successfully()
    {
        $service = new MfaService();
        $user = new User();
        $user->id = 99;

        // Seed cache
        $key = 'mfa_otp_99';
        Cache::put($key, '123456', 5);

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

        // Seed cache
        $key = 'mfa_otp_99';
        Cache::put($key, '123456', 5);

        $isVerified = $service->verifyOtp($user, '654321');
        $this->assertFalse($isVerified);
    }
}
