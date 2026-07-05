<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\PatientRepositoryInterface;
use App\Repositories\Eloquent\PatientRepository;
use App\Repositories\Contracts\AuditLogRepositoryInterface;
use App\Repositories\Eloquent\AuditLogRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PatientRepositoryInterface::class, PatientRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, AuditLogRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
