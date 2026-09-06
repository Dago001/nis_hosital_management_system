<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LabTest extends Model
{
    protected $fillable = [
        'code', 'name', 'category', 'unit',
        'ref_low', 'ref_high', 'critical_low', 'critical_high', 'is_active',
    ];

    protected $casts = [
        'ref_low' => 'float',
        'ref_high' => 'float',
        'critical_low' => 'float',
        'critical_high' => 'float',
        'is_active' => 'boolean',
    ];

    /**
     * Find the catalogue entry that best matches a free-text test name
     * (case-insensitive exact match on name or code).
     */
    public static function matchByName(?string $name): ?self
    {
        if (empty($name)) {
            return null;
        }

        $needle = mb_strtolower(trim($name));

        return static::whereRaw('LOWER(name) = ?', [$needle])
            ->orWhereRaw('LOWER(code) = ?', [$needle])
            ->first();
    }

    /**
     * Classify a numeric result value against this test's reference / critical
     * ranges. Returns one of: normal, low, high, critical_low, critical_high.
     * Returns null when the value is non-numeric or no range is defined.
     */
    public function flagFor(string $value): ?string
    {
        if (!is_numeric($value)) {
            return null;
        }

        $v = (float) $value;

        if ($this->critical_low !== null && $v <= $this->critical_low) {
            return 'critical_low';
        }
        if ($this->critical_high !== null && $v >= $this->critical_high) {
            return 'critical_high';
        }
        if ($this->ref_low !== null && $v < $this->ref_low) {
            return 'low';
        }
        if ($this->ref_high !== null && $v > $this->ref_high) {
            return 'high';
        }
        if ($this->ref_low !== null || $this->ref_high !== null) {
            return 'normal';
        }

        return null;
    }
}
