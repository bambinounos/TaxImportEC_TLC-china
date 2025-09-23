<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalculationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'calculation_id',
        'part_number',
        'description_en',
        'description_es',
        'hs_code',
        'ice_exempt',
        'ice_exempt_reason',
        'unit_weight',
        'quantity',
        'unit_price_fob',
        'total_fob_value',
        'prorated_freight',
        'prorated_insurance',
        'prorated_additional_pre_tax',
        'cif_value',
        'tariff_rate',
        'tariff_amount',
        'fodinfa_rate',
        'fodinfa_amount',
        'ice_rate',
        'ice_amount',
        'iva_rate',
        'iva_amount',
        'total_taxes',
        'prorated_additional_post_tax',
        'total_cost',
        'unit_cost',
        'sale_price',
        'unit_sale_price',
    ];

    protected $casts = [
        'ice_exempt' => 'boolean',
        'unit_weight' => 'decimal:4',
        'unit_price_fob' => 'decimal:4',
        'total_fob_value' => 'decimal:2',
        'prorated_freight' => 'decimal:4',
        'prorated_insurance' => 'decimal:4',
        'prorated_additional_pre_tax' => 'decimal:4',
        'cif_value' => 'decimal:2',
        'tariff_rate' => 'decimal:4',
        'tariff_amount' => 'decimal:4',
        'fodinfa_rate' => 'decimal:4',
        'fodinfa_amount' => 'decimal:4',
        'ice_rate' => 'decimal:4',
        'ice_amount' => 'decimal:4',
        'iva_rate' => 'decimal:4',
        'iva_amount' => 'decimal:4',
        'total_taxes' => 'decimal:4',
        'prorated_additional_post_tax' => 'decimal:4',
        'total_cost' => 'decimal:2',
        'unit_cost' => 'decimal:4',
        'sale_price' => 'decimal:2',
        'unit_sale_price' => 'decimal:4',
    ];

    public function calculation()
    {
        return $this->belongsTo(Calculation::class);
    }

    public function tariffCode()
    {
        return $this->belongsTo(TariffCode::class, 'hs_code', 'hs_code');
    }
}
