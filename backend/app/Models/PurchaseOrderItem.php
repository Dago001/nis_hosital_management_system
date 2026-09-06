<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseOrderItem extends Model
{
    protected $fillable = [
        'purchase_order_id', 'pharmacy_item_id', 'description',
        'quantity_ordered', 'quantity_received', 'unit_cost', 'line_total',
    ];

    protected $casts = [
        'unit_cost' => 'float',
        'line_total' => 'float',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function pharmacyItem(): BelongsTo
    {
        return $this->belongsTo(PharmacyItem::class);
    }
}
