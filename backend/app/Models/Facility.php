<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    protected $fillable = [
        'name', 'code', 'type', 'state', 'address', 'phone', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }
}
