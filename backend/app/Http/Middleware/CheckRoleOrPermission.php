<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleOrPermission
{
    public function handle(Request $request, Closure $next, string $roleOrPermission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // If user is super_admin, let them pass everything
        if ($user->hasRole('super_admin') || $user->hasRole('ict_admin')) {
            return $next($request);
        }

        // Check if the argument is a role or permission
        if ($user->hasRole($roleOrPermission) || $user->hasPermission($roleOrPermission)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Unauthorized. Insufficient permissions.'
        ], 403);
    }
}
