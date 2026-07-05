<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    protected $fillable = [
        'prescription_id',
        'drug_name',
        'dosage',
        'frequency',
        'duration_days',
        'quantity_prescribed',
        'quantity_dispensed',
        'instructions',
        'status',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }
}
