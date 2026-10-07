<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XssSanitizer
{
    /**
     * Input fields that must never be mutated (credentials, tokens, free-form
     * clinical narratives, and structured payloads that a blunt filter would corrupt).
     *
     * @var array<int, string>
     */
    protected array $except = [
        'password',
        'password_confirmation',
        'current_password',
        'mfa_secret',
        'code',
        'token',
        'external_api_token',
        'webhook_secret',
        'webhook_url',
        'integration_fetch_url',
    ];

    /**
     * Handle an incoming request.
     *
     * Defence-in-depth only: the primary XSS protection is Blade's automatic
     * output escaping. Here we neutralise the highest-risk vectors (HTML tags
     * and dangerous URI schemes) without destroying legitimate clinical text
     * such as "BP > 140/90" or "dose < 5mg".
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value, $key) {
            if (is_string($value) && ! in_array($key, $this->except, true)) {
                $value = $this->clean($value);
            }
        });

        $request->merge($input);

        return $next($request);
    }

    /**
     * Strip embedded HTML/script markup and defuse dangerous URI schemes
     * while preserving ordinary punctuation and comparison operators.
     */
    protected function clean(string $value): string
    {
        // Remove complete <script>/<style> blocks including their contents.
        $value = preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1>#is', '', $value) ?? $value;

        // Remove any remaining HTML tags.
        $value = preg_replace('/<[^>]*>/', '', $value) ?? $value;

        // Neutralise dangerous URI schemes and inline event handlers.
        $value = preg_replace('/\b(javascript|vbscript)\s*:/i', 'blocked:', $value) ?? $value;
        $value = preg_replace('/\bon[a-z]+\s*=\s*(["\']).*?\1/i', '', $value) ?? $value;

        return $value;
    }
}
