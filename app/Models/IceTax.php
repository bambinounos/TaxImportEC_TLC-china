<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IceTax extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_category',
        'description',
        'taxable_subjects',
        'taxable_event',
        'base_type',
        'specific_base_description',
        'specific_rate_usd',
        'advalorem_rate_percent',
        'exemptions',
        'reductions',
        'benefits',
        'is_active',
    ];

    protected $casts = [
        'specific_rate_usd' => 'decimal:4',
        'advalorem_rate_percent' => 'decimal:4',
        'is_active' => 'boolean',
    ];

    public function calculateIceAmount(float $baseValue, int $quantity = 1, string $unit = 'unit'): float
    {
        if (!$this->is_active) {
            return 0;
        }

        $amount = 0;

        if ($this->base_type === 'specific' || $this->base_type === 'both') {
            if ($this->specific_rate_usd) {
                $amount += $this->specific_rate_usd * $quantity;
            }
        }

        if ($this->base_type === 'advalorem' || $this->base_type === 'both') {
            if ($this->advalorem_rate_percent) {
                $amount += $baseValue * ($this->advalorem_rate_percent / 100);
            }
        }

        return $amount;
    }
}
