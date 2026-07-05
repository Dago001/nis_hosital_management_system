<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MfaService
{
    /**
     * Generate a 6-digit OTP code and store it in cache.
     */
    public function generateOtp(User $user): string
    {
        $otp = (string) rand(100000, 999999);
        $key = 'mfa_otp_' . $user->id;
        
        // Cache OTP for 10 minutes
        Cache::put($key, $otp, now()->addMinutes(10));
        
        // Log the OTP (so it can be retrieved from logs in local/dev environments)
        Log::info("NIS HMS MFA OTP for user {$user->email} [ID: {$user->id}]: {$otp}");
        
        return $otp;
    }

    /**
     * Verify the supplied OTP against the cached one.
     */
    public function verifyOtp(User $user, string $code): bool
    {
        $key = 'mfa_otp_' . $user->id;
        $cachedCode = Cache::get($key);
        
        if ($cachedCode && $cachedCode === $code) {
            Cache::forget($key);
            return true;
        }
        
        return false;
    }
}
