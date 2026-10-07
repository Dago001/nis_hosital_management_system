<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockIssuance extends Model
{
    protected $fillable = [
        'reference',
        'pharmacy_item_id',
        'quantity',
        'batch_number',
        'expiry_date',
        'issued_by',
        'received_by_staff_id',
        'received_by_name',
        'notes',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'expiry_date' => 'date',
        'quantity' => 'integer',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(PharmacyItem::class, 'pharmacy_item_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(Staff::class, 'received_by_staff_id');
    }
}
