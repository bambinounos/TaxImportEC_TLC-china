<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'use_tlc_china',
        'calculation_year',
        'proration_method',
        'freight_cost',
        'insurance_rate',
        'additional_costs_pre_tax',
        'additional_costs_post_tax',
        'container_count',
        'profit_margin_percent',
        'total_fob_value',
        'total_cif_value',
        'total_taxes',
        'total_final_cost',
    ];

    protected $casts = [
        'use_tlc_china' => 'boolean',
        'freight_cost' => 'decimal:2',
        'insurance_rate' => 'decimal:4',
        'additional_costs_pre_tax' => 'array',
        'additional_costs_post_tax' => 'array',
        'profit_margin_percent' => 'decimal:4',
        'total_fob_value' => 'decimal:2',
        'total_cif_value' => 'decimal:2',
        'total_taxes' => 'decimal:2',
        'total_final_cost' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CalculationItem::class);
    }

    public function getTotalAdditionalCostsPreTax(): float
    {
        if (!$this->additional_costs_pre_tax) {
            return 0;
        }

        return array_sum(array_values($this->additional_costs_pre_tax));
    }

    public function getTotalAdditionalCostsPostTax(): float
    {
        if (!$this->additional_costs_post_tax) {
            return 0;
        }

        $total = 0;
        foreach ($this->additional_costs_post_tax as $cost) {
            if (is_array($cost) && isset($cost['amount'])) {
                $amount = $cost['amount'];
                $ivaApplies = $cost['iva_applies'] ?? false;
                
                if ($ivaApplies) {
                    $amount = $amount * 1.15; // Apply 15% IVA (important-comment)
                }
                
                $total += $amount;
            } else {
                // Backward compatibility for simple numeric values
                $total += is_numeric($cost) ? $cost : 0;
            }
        }
        
        return $total;
    }
}
