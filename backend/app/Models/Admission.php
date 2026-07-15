<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Admission extends Model
{
    protected $fillable = [
        'patient_id',
        'visit_id',
        'bed_id',
        'staff_id',
        'admitted_at',
        'discharged_at',
        'discharge_summary',
        'status',
        'admission_type',
        'diagnosis_on_admission',
        'ward_notes',
        'emergency_id',
    ];

    protected $casts = [
        'admitted_at' => 'datetime',
        'discharged_at' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function emergency(): BelongsTo
    {
        return $this->belongsTo(Emergency::class);
    }
}
