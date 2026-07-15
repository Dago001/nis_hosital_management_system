<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XssSanitizer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();
        array_walk_recursive($input, function (&$value) {
            if (is_string($value)) {
                // Remove potential XSS tags, allowing only safe HTML if absolutely necessary, 
                // but here we strictly strip all tags to prevent script injection.
                $value = strip_tags($value);
                // Also remove any javascript: or data: URIs
                $value = preg_replace('/javascript:/i', 'sanitized:', $value);
                $value = preg_replace('/data:/i', 'sanitized:', $value);
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
