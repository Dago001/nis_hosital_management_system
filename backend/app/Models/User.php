<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'mfa_secret',
        'mfa_enabled',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'mfa_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mfa_enabled' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Roles (with permissions) resolved once per request and memoised on the
     * instance, so repeated RBAC checks in middleware do not re-query the DB.
     */
    protected function resolvedRoles()
    {
        if (! $this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        return $this->getRelation('roles');
    }

    public function hasRole(string $role): bool
    {
        return $this->resolvedRoles()->contains('name', $role);
    }

    public function hasPermission(string $permission): bool
    {
        return $this->resolvedRoles()
            ->pluck('permissions')
            ->flatten()
            ->contains('name', $permission);
    }

    public function staff(): HasOne
    {
        return $this->hasOne(Staff::class);
    }
}
