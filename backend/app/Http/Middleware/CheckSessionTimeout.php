<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckSessionTimeout
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user) {
            $token = $user->currentAccessToken();
            if ($token) {
                $cacheKey = 'token_last_activity_' . $token->id;
                $lastActivity = Cache::get($cacheKey);

                if ($lastActivity && now()->diffInMinutes($lastActivity) >= 30) {
                    // Revoke the token since it has been inactive for >= 30 mins
                    $token->delete();
                    Cache::forget($cacheKey);

                    return response()->json([
                        'message' => 'Your session has expired due to inactivity. Please log in again.'
                    ], 401);
                }

                // Update the last active time in the cache
                Cache::put($cacheKey, now(), now()->addHours(2));
            }
        }

        return $next($request);
    }
}
