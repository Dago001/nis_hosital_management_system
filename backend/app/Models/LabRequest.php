<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LabRequest extends Model
{
    protected $fillable = [
        'visit_id',
        'patient_id',
        'staff_id',
        'test_name',
        'clinical_indication',
        'status',
        'requested_at',
        'completed_at',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function result(): HasOne
    {
        return $this->hasOne(LabResult::class);
    }
}
