<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\CalculationItem;
use App\Services\TaxCalculationService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'profit_margin_percent' => 'nullable|numeric|min:0|max:1000',
        ]);

        DB::beginTransaction();
        
        try {
            $data = $request->all();
            $data['calculation_id'] = $calculation->id;
            
            $data['total_fob_value'] = $data['quantity'] * $data['unit_price_fob'];
            $data['cif_value'] = $data['total_fob_value'];
            $data['total_cost'] = $data['total_fob_value'];
            $data['unit_cost'] = $data['unit_price_fob'];
            $data['sale_price'] = $data['total_fob_value'];
            $data['unit_sale_price'] = $data['unit_price_fob'];

            if ($request->filled('profit_margin_percent')) {
                $data['profit_margin_percent'] = $request->profit_margin_percent;
            } else {
                $data['profit_margin_percent'] = null;
            }

            $item = CalculationItem::create($data);

            AuditService::log($calculation, 'item_added', "Item agregado: {$item->part_number}");

            \Log::info('Manual item created successfully', [
                'item_id' => $item->id,
                'part_number' => $item->part_number,
                'calculation_id' => $calculation->id
            ]);

            $this->taxCalculationService->calculateTaxes($calculation);
            
            DB::commit();

            return redirect()->route('calculations.show', $calculation)
                            ->with('success', 'Item agregado exitosamente.');
                            
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error creating manual item', [
                'calculation_id' => $calculation->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withInput()->with('error', 'Error al crear el item: ' . $e->getMessage());
        }
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
            'profit_margin_percent' => 'nullable|numeric|min:0|max:1000',
        ]);

        $data = $request->all();
        if (!$request->filled('use_custom_profit')) {
            $data['profit_margin_percent'] = null;
        }

        $calculationItem->update($data);

        AuditService::log($calculationItem->calculation, 'item_updated', "Item actualizado: {$calculationItem->part_number}");

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
