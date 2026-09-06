<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Claim extends Model
{
    protected $fillable = [
        'claim_number', 'provider', 'period_start', 'period_end', 'total_amount',
        'status', 'notes', 'created_by', 'submitted_at', 'settled_at',
    ];

    protected $casts = [
        'period_start' => 'date', 'period_end' => 'date',
        'submitted_at' => 'datetime', 'settled_at' => 'datetime',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ClaimItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
