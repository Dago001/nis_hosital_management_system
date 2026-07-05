<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    protected $fillable = [
        'lab_request_id',
        'scientist_id',
        'result_value',
        'normal_range_min',
        'normal_range_max',
        'unit',
        'status',
        'remarks',
        'approved_at',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class, 'lab_request_id');
    }

    public function scientist(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'scientist_id');
    }
}
