<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SafeSecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Safe Security Headers (Won't break local XAMPP HTTP)
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN'); // Anti-clickjacking
        $response->headers->set('X-XSS-Protection', '1; mode=block'); // Legacy anti-xss
        $response->headers->set('X-Content-Type-Options', 'nosniff'); // Anti mime-sniffing
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
