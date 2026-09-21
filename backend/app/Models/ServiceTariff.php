<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceTariff extends Model
{
    protected $fillable = ['code', 'name', 'category', 'price', 'is_active', 'description'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Resolve the active price for a tariff code, falling back to a default.
     */
    public static function priceFor(string $code, float $default = 0): float
    {
        $tariff = static::where('code', $code)->where('is_active', true)->first();
        return $tariff ? (float) $tariff->price : $default;
    }
}
