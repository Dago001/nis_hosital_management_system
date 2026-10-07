<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleOrPermission
{
    /**
     * Authorize the request if the user matches ANY of the supplied roles or
     * permissions. Usage: `role_or_permission:doctor,nurse,ward_manager` or
     * `role_or_permission:view_patients`. super_admin and ict_admin bypass.
     */
    public function handle(Request $request, Closure $next, string ...$rolesOrPermissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Global administrators bypass granular checks.
        if ($user->hasRole('super_admin') || $user->hasRole('ict_admin')) {
            return $next($request);
        }

        foreach ($rolesOrPermissions as $needle) {
            if ($user->hasRole($needle) || $user->hasPermission($needle)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Unauthorized. Insufficient permissions.'
        ], 403);
    }
}
