<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SenaeLiberation extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'type',
        'description',
        'legal_basis',
        'exempts_iva',
        'iva_reduction_percent',
        'exempts_tariff',
        'tariff_reduction_percent',
        'exempts_ice',
        'exempts_fodinfa',
        'is_active',
    ];

    protected $casts = [
        'exempts_iva' => 'boolean',
        'iva_reduction_percent' => 'decimal:2',
        'exempts_tariff' => 'boolean',
        'tariff_reduction_percent' => 'decimal:2',
        'exempts_ice' => 'boolean',
        'exempts_fodinfa' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function calculationItems()
    {
        return $this->hasMany(CalculationItem::class, 'liberation_code', 'code');
    }

    /**
     * Determine if this liberation applies full or partial IVA exemption.
     */
    public function appliesToIva(): bool
    {
        return $this->is_active && $this->exempts_iva;
    }

    /**
     * Determine if this liberation applies full or partial tariff exemption.
     */
    public function appliesToTariff(): bool
    {
        return $this->is_active && $this->exempts_tariff;
    }

    /**
     * Get a formatted display label for selects and tables.
     */
    public function getDisplayLabelAttribute(): string
    {
        $benefits = [];
        if ($this->exempts_iva) {
            $benefits[] = 'IVA 0%';
        }
        if ($this->exempts_tariff) {
            $benefits[] = 'Arancel ' . ($this->tariff_reduction_percent == 100 ? '0%' : '-' . $this->tariff_reduction_percent . '%');
        }

        $benefitStr = !empty($benefits) ? ' (' . implode(', ', $benefits) . ')' : '';

        return "{$this->code} - {$this->description}{$benefitStr}";
    }
}
