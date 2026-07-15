<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'patient_id',
        'visit_id',
        'emergency_id',
        'referring_doctor_id',
        'referring_facility',
        'receiving_facility',
        'receiving_doctor',
        'referral_type',
        'priority',
        'reason',
        'clinical_summary',
        'special_instructions',
        'status',
        'referred_at',
        'accepted_at',
        'completed_at',
    ];

    protected $casts = [
        'referred_at' => 'datetime',
        'accepted_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class);
    }

    public function referringDoctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'referring_doctor_id');
    }
}
