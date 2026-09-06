<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdmissionObservation extends Model
{
    protected $fillable = [
        'admission_id', 'recorded_by', 'blood_pressure', 'temperature', 'pulse_rate',
        'respiratory_rate', 'spo2', 'fluid_intake_ml', 'fluid_output_ml', 'news_score',
        'notes', 'recorded_at',
    ];

    protected $casts = ['recorded_at' => 'datetime'];

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'recorded_by');
    }
}
