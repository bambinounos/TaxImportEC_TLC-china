<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\CalculationItem;
use App\Services\TaxCalculationService;
use Illuminate\Http\Request;

class CalculationItemController extends Controller
{
    protected TaxCalculationService $taxCalculationService;

    public function __construct(TaxCalculationService $taxCalculationService)
    {
        $this->taxCalculationService = $taxCalculationService;
    }

    public function store(Request $request, Calculation $calculation)
    {
        $this->authorize('update', $calculation);

        $request->validate([
            'part_number' => 'required|string|max:255|unique:calculation_items,part_number,NULL,id,calculation_id,'.$calculation->id,
            'description_en' => 'required|string|max:255',
            'description_es' => 'nullable|string|max:255',
            'hs_code' => 'nullable|string|max:20|exists:tariff_codes,hs_code',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price_fob' => 'required|numeric|min:0.01',
            'unit_weight' => 'nullable|numeric|min:0',
            'ice_exempt' => 'nullable|boolean',
            'ice_exempt_reason' => 'nullable|string|max:255',
        ]);

        $data = $request->all();
        $data['calculation_id'] = $calculation->id;

        CalculationItem::create($data);

        $this->taxCalculationService->calculateTaxes($calculation);

        return redirect()->route('calculations.show', $calculation)
                        ->with('success', 'Item agregado exitosamente.');
    }

    public function edit(CalculationItem $calculationItem)
    {
        $this->authorize('update', $calculationItem->calculation);

        return view('calculations.edit-item', compact('calculationItem'));
    }

    public function update(Request $request, CalculationItem $calculationItem)
    {
        $this->authorize('update', $calculationItem->calculation);

        $request->validate([
            'part_number' => 'required|string|max:255|unique:calculation_items,part_number,'.$calculationItem->id.',id,calculation_id,'.$calculationItem->calculation_id,
            'description_en' => 'required|string|max:255',
            'description_es' => 'nullable|string|max:255',
            'hs_code' => 'nullable|string|max:20|exists:tariff_codes,hs_code',
            'quantity' => 'required|numeric|min:0.01',
            'unit_price_fob' => 'required|numeric|min:0.01',
            'unit_weight' => 'nullable|numeric|min:0',
            'ice_exempt' => 'nullable|boolean',
            'ice_exempt_reason' => 'nullable|string|max:255',
        ]);

        $calculationItem->update($request->all());

        $this->taxCalculationService->calculateTaxes($calculationItem->calculation);

        return redirect()->route('calculations.show', $calculationItem->calculation)
                        ->with('success', 'Item actualizado exitosamente.');
    }

    public function destroy(CalculationItem $calculationItem)
    {
        $this->authorize('update', $calculationItem->calculation);

        $calculation = $calculationItem->calculation;
        $calculationItem->delete();

        $this->taxCalculationService->calculateTaxes($calculation);

        return redirect()->route('calculations.show', $calculation)
                        ->with('success', 'Item eliminado exitosamente.');
    }
}
