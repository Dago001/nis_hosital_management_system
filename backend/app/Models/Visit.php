<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Visit extends Model
{
    protected $fillable = [
        'patient_id',
        'staff_id',
        'department_id',
        'vitals_blood_pressure',
        'vitals_temperature',
        'vitals_pulse_rate',
        'vitals_respiratory_rate',
        'vitals_weight',
        'vitals_height',
        'chief_complaint',
        'history',
        'soap_notes_subjective',
        'soap_notes_objective',
        'soap_notes_assessment',
        'soap_notes_plan',
        'diagnosis_icd10',
        'diagnosis_description',
        'treatment_plan',
        'follow_up_date',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class);
    }

    public function radiologyRequests(): HasMany
    {
        return $this->hasMany(RadiologyRequest::class);
    }

    public function admission(): HasOne
    {
        return $this->hasOne(Admission::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }
}
