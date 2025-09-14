<?php

namespace App\Services;

use App\Models\Calculation;
use App\Models\CalculationItem;
use App\Models\TariffCode;
use App\Models\IceTax;
use App\Models\SystemSetting;

class TaxCalculationService
{
    public function calculateTaxes(Calculation $calculation): void
    {
        $this->updateCalculationTotals($calculation);
        
        foreach ($calculation->items as $item) {
            $this->calculateItemTaxes($item, $calculation);
        }
        
        $this->updateCalculationTotals($calculation);
    }

    protected function calculateItemTaxes(CalculationItem $item, Calculation $calculation): void
    {
        $this->proratePreTaxCosts($item, $calculation);
        
        $item->cif_value = $item->total_fob_value + $item->prorated_freight + 
                          $item->prorated_insurance + $item->prorated_additional_pre_tax;

        $this->calculateTariff($item, $calculation);
        $this->calculateIce($item);
        $this->calculateIva($item);
        
        $item->total_taxes = $item->tariff_amount + $item->ice_amount + $item->iva_amount;
        
        $this->proratePostTaxCosts($item, $calculation);
        
        $item->total_cost = $item->cif_value + $item->total_taxes + $item->prorated_additional_post_tax;
        $item->unit_cost = $item->total_cost / $item->quantity;
        
        $profitMargin = $calculation->profit_margin_percent / 100;
        $item->sale_price = $item->total_cost * (1 + $profitMargin);
        $item->unit_sale_price = $item->sale_price / $item->quantity;
        
        $item->save();
    }

    protected function proratePreTaxCosts(CalculationItem $item, Calculation $calculation): void
    {
        $totalForProration = $calculation->items->sum('total_fob_value');
        
        if ($totalForProration <= 0) {
            return;
        }

        $prorationFactor = $this->getProrationFactor($item, $calculation, $totalForProration);
        
        $item->prorated_freight = $calculation->freight_cost * $prorationFactor;
        $item->prorated_insurance = $item->total_fob_value * ($calculation->insurance_rate / 100);
        
        $totalAdditionalPreTax = $calculation->getTotalAdditionalCostsPreTax();
        $item->prorated_additional_pre_tax = $totalAdditionalPreTax * $prorationFactor;
    }

    protected function proratePostTaxCosts(CalculationItem $item, Calculation $calculation): void
    {
        $totalForProration = $calculation->items->sum('total_fob_value');
        
        if ($totalForProration <= 0) {
            return;
        }

        $prorationFactor = $this->getProrationFactor($item, $calculation, $totalForProration);
        
        $totalAdditionalPostTax = $calculation->getTotalAdditionalCostsPostTax();
        $item->prorated_additional_post_tax = $totalAdditionalPostTax * $prorationFactor;
    }

    protected function getProrationFactor(CalculationItem $item, Calculation $calculation, float $totalForProration): float
    {
        if ($calculation->proration_method === 'weight') {
            $totalWeight = $calculation->items->sum(function ($i) {
                return $i->unit_weight * $i->quantity;
            });
            
            if ($totalWeight > 0) {
                return ($item->unit_weight * $item->quantity) / $totalWeight;
            }
        }
        
        return $item->total_fob_value / $totalForProration;
    }

    protected function calculateTariff(CalculationItem $item, Calculation $calculation): void
    {
        if (!$item->hs_code) {
            $item->tariff_rate = 0;
            $item->tariff_amount = 0;
            return;
        }

        $tariffCode = TariffCode::where('hs_code', $item->hs_code)->first();
        
        if (!$tariffCode) {
            $item->tariff_rate = 0;
            $item->tariff_amount = 0;
            return;
        }

        if ($calculation->use_tlc_china) {
            $item->tariff_rate = $tariffCode->getEffectiveTariffRate('CHN', $calculation->calculation_year);
        } else {
            $item->tariff_rate = $tariffCode->base_tariff_rate;
        }

        $item->tariff_amount = $item->cif_value * ($item->tariff_rate / 100);
    }

    protected function calculateIce(CalculationItem $item): void
    {
        if ($item->ice_exempt) {
            $item->ice_rate = 0;
            $item->ice_amount = 0;
            return;
        }

        if (!$item->hs_code) {
            $item->ice_rate = 0;
            $item->ice_amount = 0;
            return;
        }

        $tariffCode = TariffCode::where('hs_code', $item->hs_code)->first();
        
        if (!$tariffCode || !$tariffCode->has_ice) {
            $item->ice_rate = 0;
            $item->ice_amount = 0;
            return;
        }

        $iceTax = IceTax::where('is_active', true)
            ->where('product_category', 'LIKE', '%' . substr($item->hs_code, 0, 4) . '%')
            ->first();

        if (!$iceTax) {
            $item->ice_rate = 0;
            $item->ice_amount = 0;
            return;
        }

        $baseValue = $item->cif_value + $item->tariff_amount;
        $item->ice_amount = $iceTax->calculateIceAmount($baseValue, $item->quantity);
        
        if ($iceTax->base_type === 'advalorem' && $iceTax->advalorem_rate_percent) {
            $item->ice_rate = $iceTax->advalorem_rate_percent;
        } else {
            $item->ice_rate = $item->ice_amount > 0 ? ($item->ice_amount / $baseValue) * 100 : 0;
        }
    }

    protected function calculateIva(CalculationItem $item): void
    {
        $tariffCode = null;
        
        if ($item->hs_code) {
            $tariffCode = TariffCode::where('hs_code', $item->hs_code)->first();
        }

        $ivaRate = $tariffCode ? $tariffCode->iva_rate : SystemSetting::get('default_iva_rate', 15.0);
        
        $item->iva_rate = $ivaRate;
        
        $ivaBase = $item->cif_value + $item->tariff_amount + $item->ice_amount;
        $item->iva_amount = $ivaBase * ($item->iva_rate / 100);
    }

    protected function updateCalculationTotals(Calculation $calculation): void
    {
        $calculation->load('items');
        
        $calculation->total_fob_value = $calculation->items->sum('total_fob_value');
        $calculation->total_cif_value = $calculation->items->sum('cif_value');
        $calculation->total_taxes = $calculation->items->sum('total_taxes');
        $calculation->total_final_cost = $calculation->items->sum('total_cost');
        
        $calculation->save();
    }
}
