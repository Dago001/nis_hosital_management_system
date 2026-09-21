<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Services\MfaService;

class AuthController extends Controller
{
    protected MfaService $mfaService;

    /**
     * A pre-computed bcrypt hash used to run a dummy verification when the
     * submitted email does not exist. This keeps the response time for
     * "unknown user" and "wrong password" roughly equal, defeating the
     * timing side-channel that would otherwise let an attacker enumerate
     * which email addresses are registered.
     */
    private const DUMMY_HASH = '$2y$12$MgcfgKUY0F.EkOScALNV9.qHmSwMAwp6M4JwMWuq.zq9I9aOO2krW';

    public function __construct(MfaService $mfaService)
    {
        $this->mfaService = $mfaService;
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Progressive brute-force / credential-stuffing lockout, keyed on
        // email + IP (see the 'login' limiter in AppServiceProvider).
        $throttleKey = 'login:'.mb_strtolower((string) $request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return response()->json([
                'message' => 'Too many login attempts. Please try again in '
                    . RateLimiter::availableIn($throttleKey) . ' seconds.',
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        // Always run a hash check (against a dummy hash for unknown users) so the
        // timing profile does not reveal whether the account exists.
        $passwordOk = Hash::check($request->password, $user?->password ?? self::DUMMY_HASH);

        if (!$user || !$passwordOk) {
            RateLimiter::hit($throttleKey, 900); // remember failures for 15 minutes
            return response()->json([
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }

        if ($user->status !== 'active') {
            RateLimiter::hit($throttleKey, 900);
            return response()->json([
                'message' => 'Your account has been suspended. Please contact the administrator.'
            ], 403);
        }

        // Successful credential check — clear the failure counter.
        RateLimiter::clear($throttleKey);

        // Check if MFA is enabled.
        // NOTE: MFA is temporarily bypassed for the Cashier role while testing.
        //       Remove `&& !$user->hasRole('cashier')` to re-enable it for cashiers.
        if ($user->mfa_enabled && !$user->hasRole('cashier')) {
            // Generate OTP
            $this->mfaService->generateOtp($user);

            return response()->json([
                'mfa_required' => true,
                'email' => $user->email,
                'message' => 'Multi-Factor Authentication code has been sent to your registered email.'
            ]);
        }

        // Generate Sanctum Token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user)
        ]);
    }

    public function verifyMfa(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        // Lock out repeated OTP guessing (email + IP).
        $throttleKey = 'mfa:'.mb_strtolower((string) $request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 6)) {
            return response()->json([
                'message' => 'Too many verification attempts. Please try again in '
                    . RateLimiter::availableIn($throttleKey) . ' seconds.',
            ], 429);
        }

        $user = User::where('email', $request->email)->first();

        // Return an identical error whether the user exists or the code is wrong
        // so this endpoint cannot be used to enumerate accounts.
        if (!$user || !$this->mfaService->verifyOtp($user, $request->code)) {
            RateLimiter::hit($throttleKey, 900);
            return response()->json([
                'message' => 'Invalid or expired MFA passcode.'
            ], 422);
        }

        RateLimiter::clear($throttleKey);

        // Verification successful, return Sanctum token
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => new UserResource($user)
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->name = $validated['name'];
        $user->save();

        if ($user->staff && isset($validated['phone'])) {
            $user->staff->phone = $validated['phone'];
            $user->staff->save();
        }

        return response()->json([
            'message' => 'Profile updated successfully.',
            'user' => new UserResource($user->load(['roles', 'staff.department']))
        ]);
    }

    public function uploadAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = $request->user();

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();
        }

        return response()->json([
            'message' => 'Avatar uploaded successfully.',
            'user' => new UserResource($user->load(['roles', 'staff.department']))
        ]);
    }

    public function toggleMfa(Request $request)
    {
        $user = $request->user();
        $user->mfa_enabled = !$user->mfa_enabled;
        $user->save();

        return response()->json([
            'message' => $user->mfa_enabled ? 'MFA has been enabled.' : 'MFA has been disabled.',
            'mfa_enabled' => $user->mfa_enabled
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json([
            'user' => new UserResource($request->user())
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out.'
        ]);
    }
}
