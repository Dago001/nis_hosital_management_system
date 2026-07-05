<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RadiologyResult extends Model
{
    protected $fillable = [
        'radiology_request_id',
        'radiographer_id',
        'image_path',
        'report_text',
        'status',
        'approved_at',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(RadiologyRequest::class, 'radiology_request_id');
    }

    public function radiographer(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'radiographer_id');
    }
}
