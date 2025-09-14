<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TariffCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'hs_code',
        'description_en',
        'description_es',
        'base_tariff_rate',
        'iva_rate',
        'has_ice',
        'is_active',
    ];

    protected $casts = [
        'base_tariff_rate' => 'decimal:4',
        'iva_rate' => 'decimal:4',
        'has_ice' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tlcSchedules()
    {
        return $this->hasMany(TlcSchedule::class, 'hs_code', 'hs_code');
    }

    public function calculationItems()
    {
        return $this->hasMany(CalculationItem::class, 'hs_code', 'hs_code');
    }

    public function getEffectiveTariffRate(string $countryCode = 'CHN', int $year = null): float
    {
        $year = $year ?? date('Y');
        
        $tlcSchedule = $this->tlcSchedules()
            ->where('country_code', $countryCode)
            ->where('is_active', true)
            ->first();

        if (!$tlcSchedule) {
            return (float) $this->base_tariff_rate;
        }

        return $tlcSchedule->getEffectiveRate($year);
    }
}
