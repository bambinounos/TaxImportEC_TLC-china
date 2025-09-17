<?php

namespace App\Http\Controllers;

use App\Models\Calculation;
use App\Models\CalculationItem;
use App\Services\TaxCalculationService;
use App\Services\CsvImportService;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CalculationController extends Controller
{
    protected TaxCalculationService $taxCalculationService;
    protected CsvImportService $csvImportService;
    protected CsvExportService $csvExportService;

    public function __construct(
        TaxCalculationService $taxCalculationService,
        CsvImportService $csvImportService,
        CsvExportService $csvExportService
    ) {
        $this->taxCalculationService = $taxCalculationService;
        $this->csvImportService = $csvImportService;
        $this->csvExportService = $csvExportService;
    }

    public function index()
    {
        $calculations = Auth::user()->calculations()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('calculations.index', compact('calculations'));
    }

    public function create()
    {
        try {
            return view('calculations.create');
        } catch (\Exception $e) {
            \Log::error('Error in CalculationController@create: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Error al cargar el formulario de cálculo: ' . $e->getMessage());
        }
    }

    public function createManual()
    {
        return view('calculations.create-manual');
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'calculation_name' => 'required|string|max:255',
            'products' => 'required|array|min:1',
            'products.*.part_number' => 'nullable|string|max:255',
            'products.*.description_en' => 'required|string|max:255',
            'products.*.description_es' => 'nullable|string|max:255',
            'products.*.hs_code' => 'nullable|string|max:20|exists:tariff_codes,hs_code',
            'products.*.quantity' => 'required|numeric|min:0.01',
            'products.*.unit_price_fob' => 'required|numeric|min:0.01',
            'products.*.unit_weight' => 'nullable|numeric|min:0',
            'products.*.ice_exempt' => 'nullable|boolean',
            'products.*.ice_exempt_reason' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $calculation = Calculation::create([
                'name' => $request->calculation_name,
                'user_id' => auth()->id(),
                'status' => 'draft',
            ]);

            foreach ($request->products as $productData) {
                CalculationItem::create([
                    'calculation_id' => $calculation->id,
                    'part_number' => $productData['part_number'] ?? null,
                    'description_en' => $productData['description_en'],
                    'description_es' => $productData['description_es'] ?? null,
                    'hs_code' => $productData['hs_code'] ?? null,
                    'quantity' => $productData['quantity'],
                    'unit_price_fob' => $productData['unit_price_fob'],
                    'total_fob_value' => $productData['quantity'] * $productData['unit_price_fob'],
                    'unit_weight' => $productData['unit_weight'] ?? null,
                    'ice_exempt' => $productData['ice_exempt'] ?? false,
                    'ice_exempt_reason' => $productData['ice_exempt_reason'] ?? null,
                ]);
            }

            $this->taxCalculationService->calculateTaxes($calculation);
            
            DB::commit();

            return redirect()->route('calculations.show', $calculation)
                            ->with('success', 'Cálculo manual creado y procesado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error al crear el cálculo manual: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'use_tlc_china' => 'boolean',
            'calculation_year' => 'required|integer|min:2024|max:2050',
            'proration_method' => 'required|in:weight,price',
            'freight_cost' => 'required|numeric|min:0',
            'insurance_rate' => 'required|numeric|min:0|max:100',
            'profit_margin_percent' => 'required|numeric|min:0|max:1000',
        ]);

        $calculation = Calculation::create([
            'name' => $request->name,
            'description' => $request->description,
            'user_id' => Auth::id(),
            'use_tlc_china' => $request->boolean('use_tlc_china'),
            'calculation_year' => $request->calculation_year,
            'proration_method' => $request->proration_method,
            'freight_cost' => $request->freight_cost,
            'insurance_rate' => $request->insurance_rate,
            'profit_margin_percent' => $request->profit_margin_percent,
        ]);

        return redirect()->route('calculations.show', $calculation)
            ->with('success', 'Cálculo creado exitosamente.');
    }

    public function show(Calculation $calculation)
    {
        $this->authorize('view', $calculation);
        
        $calculation->load('items.tariffCode');
        
        return view('calculations.show', compact('calculation'));
    }

    public function importCsv(Request $request, Calculation $calculation)
    {
        $this->authorize('update', $calculation);

        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        try {
            DB::beginTransaction();

            $results = $this->csvImportService->importFromCsv(
                $request->file('csv_file'),
                $calculation
            );

            if ($results['success'] > 0) {
                $this->taxCalculationService->calculateTaxes($calculation);
            }

            DB::commit();

            $message = "Importación completada: {$results['success']} productos importados.";
            
            if (!empty($results['warnings'])) {
                $message .= " Advertencias: " . implode(', ', $results['warnings']);
            }

            if (!empty($results['errors'])) {
                $message .= " Errores: " . implode(', ', $results['errors']);
            }

            return redirect()->route('calculations.show', $calculation)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()->route('calculations.show', $calculation)
                ->with('error', 'Error durante la importación: ' . $e->getMessage());
        }
    }

    public function calculate(Calculation $calculation)
    {
        $this->authorize('update', $calculation);

        try {
            $this->taxCalculationService->calculateTaxes($calculation);

            return redirect()->route('calculations.show', $calculation)
                ->with('success', 'Cálculo de impuestos completado exitosamente.');

        } catch (\Exception $e) {
            return redirect()->route('calculations.show', $calculation)
                ->with('error', 'Error durante el cálculo: ' . $e->getMessage());
        }
    }

    public function exportCsv(Calculation $calculation)
    {
        $this->authorize('view', $calculation);

        try {
            $filename = $this->csvExportService->exportCalculationToCsv($calculation);
            
            return response()->download($filename)->deleteFileAfterSend();

        } catch (\Exception $e) {
            return redirect()->route('calculations.show', $calculation)
                ->with('error', 'Error durante la exportación: ' . $e->getMessage());
        }
    }

    public function exportExcel(Calculation $calculation)
    {
        $this->authorize('view', $calculation);

        try {
            $filename = $this->csvExportService->exportCalculationToExcel($calculation);
            
            return response()->download($filename)->deleteFileAfterSend();

        } catch (\Exception $e) {
            return redirect()->route('calculations.show', $calculation)
                ->with('error', 'Error durante la exportación: ' . $e->getMessage());
        }
    }

    public function destroy(Calculation $calculation)
    {
        $this->authorize('delete', $calculation);

        $calculation->delete();

        return redirect()->route('calculations.index')
            ->with('success', 'Cálculo eliminado exitosamente.');
    }
}
