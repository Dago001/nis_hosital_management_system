<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class MfaService
{
    /**
     * Generate a cryptographically secure 6-digit OTP, store a hash of it,
     * deliver it to the user, and return the plaintext code to the caller.
     */
    public function generateOtp(User $user): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $key = $this->cacheKey($user);

        // Store only a hash of the OTP so a cache/Redis leak never exposes live codes.
        Cache::put($key, Hash::make($otp), now()->addMinutes(10));

        // Deliver the code to the user's registered email.
        $this->deliverOtp($user, $otp);

        // Never log OTPs in production. Outside production, log for developer convenience only.
        if (! app()->environment('production')) {
            Log::info("NIS HMS MFA OTP for user {$user->email} [ID: {$user->id}]: {$otp}");
        }

        return $otp;
    }

    /**
     * Verify the supplied OTP against the cached hash in constant time.
     */
    public function verifyOtp(User $user, string $code): bool
    {
        $key = $this->cacheKey($user);
        $cachedHash = Cache::get($key);

        if ($cachedHash && Hash::check($code, $cachedHash)) {
            Cache::forget($key);
            return true;
        }

        return false;
    }

    /**
     * Deliver the OTP to the user via email. Failures are logged but never
     * surfaced to the caller so the login flow degrades gracefully.
     */
    protected function deliverOtp(User $user, string $otp): void
    {
        try {
            Mail::raw(
                "Your NIS HMS one-time verification code is: {$otp}\n\n"
                . "This code expires in 10 minutes. If you did not attempt to sign in, "
                . "please contact the ICT administrator immediately.",
                function ($message) use ($user) {
                    $message->to($user->email)
                        ->subject('NIS HMS Secure Login Code');
                }
            );
        } catch (\Throwable $e) {
            Log::warning("Failed to deliver MFA OTP email to {$user->email}: {$e->getMessage()}");
        }
    }

    protected function cacheKey(User $user): string
    {
        return 'mfa_otp_' . $user->id;
    }
}
