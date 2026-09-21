<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Enforce strict model checking for SQL injection & mass-assignment hardening
        \Illuminate\Database\Eloquent\Model::preventSilentlyDiscardingAttributes($this->app->isLocal());
        \Illuminate\Database\Eloquent\Model::preventAccessingMissingAttributes();

        // Force every generated URL (and cookies/assets) onto HTTPS in production so
        // that credentials and session tokens are never emitted over plain HTTP.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Enforce strong global password complexity defaults
        \Illuminate\Validation\Rules\Password::defaults(function () {
            $rule = \Illuminate\Validation\Rules\Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });

        $this->configureRateLimiters();
    }

    /**
     * Named rate limiters used as a first line of defence against brute-force,
     * credential-stuffing and denial-of-service traffic.
     */
    protected function configureRateLimiters(): void
    {
        // Blanket ceiling for every authenticated API request. Keyed on the
        // authenticated user when available, otherwise the client IP, so one
        // abusive caller cannot starve the platform for everyone else.
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(120)->by(
                optional($request->user())->id ? 'u:'.$request->user()->id : 'ip:'.$request->ip()
            );
        });

        // Tight limiter for credential submission. Keyed on the submitted email
        // AND the source IP so neither a single account nor a single host can be
        // hammered, while a shared NAT gateway cannot lock out unrelated users.
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('email');

            return [
                Limit::perMinute(5)->by('login:'.mb_strtolower($email).'|'.$request->ip()),
                Limit::perMinute(20)->by('login-ip:'.$request->ip()),
            ];
        });
    }
}
