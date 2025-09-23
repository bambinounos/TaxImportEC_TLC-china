<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TlcSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'hs_code',
        'country_code',
        'base_rate',
        'elimination_years',
        'start_date',
        'reduction_type',
        'yearly_rates',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'base_rate' => 'decimal:4',
        'start_date' => 'date',
        'yearly_rates' => 'array',
        'is_active' => 'boolean',
    ];

    public function tariffCode()
    {
        return $this->belongsTo(TariffCode::class, 'hs_code', 'hs_code');
    }

    public function getEffectiveRate(int $year): float
    {
        if (!$this->is_active) {
            return (float) $this->base_rate;
        }

        if ($this->tlc_category === 'A0' || $this->reduction_type === 'immediate') {
            return 0.0;
        }

        $startYear = $this->start_date->year;
        $yearsElapsed = $year - $startYear;

        if ($yearsElapsed < 0) {
            return (float) $this->base_rate;
        }

        if ($yearsElapsed >= $this->elimination_years) {
            return 0.0;
        }

        if ($this->yearly_rates && isset($this->yearly_rates[$year])) {
            return (float) $this->yearly_rates[$year];
        }

        if ($this->reduction_type === 'linear') {
            $reductionPerYear = $this->base_rate / $this->elimination_years;
            return max(0, $this->base_rate - ($reductionPerYear * $yearsElapsed));
        }

        return (float) $this->base_rate;
    }
}
