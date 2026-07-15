<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Emergency extends Model
{
    protected $fillable = [
        'patient_id',
        'staff_id',
        'triaged_by',
        'triage_level',
        'chief_complaint',
        'presenting_symptoms',
        'vitals_bp',
        'vitals_temp',
        'vitals_pulse',
        'vitals_spo2',
        'vitals_gcs',
        'mode_of_arrival',
        'status',
        'treatment_notes',
        'disposition_notes',
        'arrived_at',
        'triaged_at',
        'treatment_started_at',
        'resolved_at',
    ];

    protected $casts = [
        'arrived_at' => 'datetime',
        'triaged_at' => 'datetime',
        'treatment_started_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function triageNurse(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'triaged_by');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }
}
