<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Surgery extends Model
{
    protected $fillable = [
        'patient_id', 'surgeon_id', 'theatre_id', 'procedure_name',
        'scheduled_start', 'scheduled_end', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'scheduled_start' => 'datetime',
        'scheduled_end' => 'datetime',
    ];

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function surgeon(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'surgeon_id');
    }

    public function theatre(): BelongsTo
    {
        return $this->belongsTo(Theatre::class);
    }
}
