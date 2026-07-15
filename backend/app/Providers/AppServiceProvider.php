<?php

namespace App\Providers;

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

        // Enforce strong global password complexity defaults
        \Illuminate\Validation\Rules\Password::defaults(function () {
            $rule = \Illuminate\Validation\Rules\Password::min(8)
                ->letters()
                ->mixedCase()
                ->numbers()
                ->symbols();

            return $this->app->isProduction() ? $rule->uncompromised() : $rule;
        });
    }
}
