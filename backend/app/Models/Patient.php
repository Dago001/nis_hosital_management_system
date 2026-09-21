<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'facility_id',
        'first_name',
        'middle_name',
        'last_name',
        'gender',
        'marital_status',
        'occupation',
        'religion',
        'place_of_birth',
        'tribe',
        'date_of_birth',
        'phone',
        'email',
        'address',
        'state',
        'lga',
        'city',
        'next_of_kin_name',
        'next_of_kin_relationship',
        'next_of_kin_address',
        'immigration_service_number',
        'sponsor_service_number',
        'is_nhis',
        'nhis_number',
        'relationship_to_sponsor',
        'nin',
        'passport_photograph_path',
        'qr_code_data',
        'barcode_data',
        'allergies',
        'blood_group',
        'genotype',
        'disability',
    ];

    public function facility(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function dependants(): HasMany
    {
        return $this->hasMany(Patient::class, 'sponsor_service_number', 'immigration_service_number');
    }

    public function sponsor()
    {
        return $this->belongsTo(Patient::class, 'sponsor_service_number', 'immigration_service_number');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function getFullNameAttribute(): string
    {
        return $this->middle_name
            ? "{$this->first_name} {$this->middle_name} {$this->last_name}"
            : "{$this->first_name} {$this->last_name}";
    }

    /**
     * Age in whole years, derived from date_of_birth.
     */
    public function getAgeAttribute(): ?int
    {
        if (empty($this->date_of_birth)) {
            return null;
        }

        return \Illuminate\Support\Carbon::parse($this->date_of_birth)->age;
    }

    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class);
    }

    public function radiologyRequests(): HasMany
    {
        return $this->hasMany(RadiologyRequest::class);
    }

    protected function casts(): array
    {
        return [
            'is_nhis' => 'boolean',
        ];
    }

    /**
     * NHIS coverage is now an explicit flag set at registration, so the whole
     * application (billing discounts, registration fee waiver, claims
     * eligibility) treats the patient consistently.
     */
    public function isNhis(): bool
    {
        return (bool) $this->is_nhis;
    }
}
