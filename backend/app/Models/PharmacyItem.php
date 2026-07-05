<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PharmacyItem extends Model
{
    protected $fillable = [
        'name',
        'generic_name',
        'code',
        'category',
        'batch_number',
        'expiry_date',
        'quantity_in_stock',
        'reorder_level',
        'price_per_unit',
    ];
}
