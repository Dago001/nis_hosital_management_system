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
        'invoice_id',
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

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /** True when there is no bill, or the linked invoice is fully paid. */
    public function isPaid(): bool
    {
        if (! $this->invoice_id) {
            return true; // no charge attached
        }
        return $this->invoice && $this->invoice->status === 'paid';
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
