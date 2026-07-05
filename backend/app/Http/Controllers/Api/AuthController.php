<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Services\MfaService;

class AuthController extends Controller
{
    protected MfaService $mfaService;

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

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'The provided credentials do not match our records.'
            ], 422);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account has been suspended. Please contact the administrator.'
            ], 403);
        }

        // Check if MFA is enabled
        if ($user->mfa_enabled) {
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

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        if (!$this->mfaService->verifyOtp($user, $request->code)) {
            return response()->json([
                'message' => 'Invalid or expired MFA passcode.'
            ], 422);
        }

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
