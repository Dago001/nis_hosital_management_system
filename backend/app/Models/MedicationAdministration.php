<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MedicationAdministration extends Model
{
    protected $fillable = [
        'admission_id', 'prescription_item_id', 'administered_by', 'drug_name',
        'dose', 'route', 'status', 'notes', 'administered_at',
    ];

    protected $casts = ['administered_at' => 'datetime'];

    public function administrator(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'administered_by');
    }
}
