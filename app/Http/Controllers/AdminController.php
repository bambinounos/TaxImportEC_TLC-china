<?php

namespace App\Http\Controllers;

use App\Models\SystemSetting;
use App\Models\TariffCode;
use App\Models\TlcSchedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->isAdmin()) {
                abort(403, 'Acceso denegado. Se requieren permisos de administrador.');
            }
            return $next($request);
        });
    }

    /**
     * Display the admin dashboard
     */
    public function index(): View
    {
        $stats = [
            'total_tariff_codes' => TariffCode::count(),
            'active_tariff_codes' => TariffCode::where('is_active', true)->count(),
            'total_tlc_schedules' => TlcSchedule::count(),
            'active_tlc_schedules' => TlcSchedule::where('is_active', true)->count(),
        ];

        return view('admin.index', compact('stats'));
    }

    /**
     * Display tariff codes management
     */
    public function tariffCodes(Request $request): View
    {
        $query = TariffCode::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('hs_code', 'like', "%{$search}%")
                  ->orWhere('description_en', 'like', "%{$search}%")
                  ->orWhere('description_es', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->get('status') === 'active');
        }

        if ($request->filled('has_ice')) {
            $query->where('has_ice', $request->get('has_ice') === 'yes');
        }

        $tariffCodes = $query->with('tlcSchedules')
                           ->orderBy('hierarchy_level')
                           ->orderBy('hs_code')
                           ->paginate(50);

        return view('admin.tariff-codes', compact('tariffCodes'));
    }

    /**
     * Show specific tariff code with TLC schedules
     */
    public function showTariffCode(string $hsCode): View
    {
        $tariffCode = TariffCode::where('hs_code', $hsCode)
                               ->with(['tlcSchedules' => function ($query) {
                                   $query->orderBy('country_code')->orderBy('start_date');
                               }])
                               ->firstOrFail();

        return view('admin.tariff-code-detail', compact('tariffCode'));
    }

    /**
     * Show form for creating new tariff code
     */
    public function createTariffCode(): View
    {
        return view('admin.tariff-code-create');
    }

    /**
     * Store new tariff code
     */
    public function storeTariffCode(Request $request)
    {
        $request->validate([
            'hs_code' => 'required|string|max:20|unique:tariff_codes',
            'description_en' => 'required|string|max:500',
            'description_es' => 'required|string|max:500',
            'base_tariff_rate' => 'nullable|numeric|min:0|max:100',
            'iva_rate' => 'required|numeric|min:0|max:100',
            'unit' => 'nullable|string|max:10',
            'hierarchy_level' => 'required|integer|in:4,6,10',
            'parent_code' => 'nullable|string|max:20',
            'order_number' => 'nullable|integer',
            'has_ice' => 'boolean',
            'is_active' => 'boolean'
        ]);

        TariffCode::create([
            'hs_code' => $request->hs_code,
            'description_en' => $request->description_en,
            'description_es' => $request->description_es,
            'base_tariff_rate' => $request->base_tariff_rate,
            'iva_rate' => $request->iva_rate ?? 15.0,
            'unit' => $request->unit,
            'hierarchy_level' => $request->hierarchy_level,
            'parent_code' => $request->parent_code,
            'order_number' => $request->order_number,
            'has_ice' => $request->boolean('has_ice', false),
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.tariff-codes')
            ->with('success', 'Código arancelario creado exitosamente.');
    }

    /**
     * Show form for editing tariff code
     */
    public function editTariffCode(string $hsCode): View
    {
        $tariffCode = TariffCode::where('hs_code', $hsCode)->firstOrFail();
        return view('admin.tariff-code-edit', compact('tariffCode'));
    }

    /**
     * Update tariff code
     */
    public function updateTariffCode(Request $request, string $hsCode)
    {
        $tariffCode = TariffCode::where('hs_code', $hsCode)->firstOrFail();
        
        $request->validate([
            'hs_code' => 'required|string|max:20|unique:tariff_codes,hs_code,' . $tariffCode->id,
            'description_en' => 'required|string|max:500',
            'description_es' => 'required|string|max:500',
            'base_tariff_rate' => 'nullable|numeric|min:0|max:100',
            'iva_rate' => 'required|numeric|min:0|max:100',
            'unit' => 'nullable|string|max:10',
            'hierarchy_level' => 'required|integer|in:4,6,10',
            'parent_code' => 'nullable|string|max:20',
            'order_number' => 'nullable|integer',
            'has_ice' => 'boolean',
            'is_active' => 'boolean'
        ]);

        $tariffCode->update([
            'hs_code' => $request->hs_code,
            'description_en' => $request->description_en,
            'description_es' => $request->description_es,
            'base_tariff_rate' => $request->base_tariff_rate,
            'iva_rate' => $request->iva_rate,
            'unit' => $request->unit,
            'hierarchy_level' => $request->hierarchy_level,
            'parent_code' => $request->parent_code,
            'order_number' => $request->order_number,
            'has_ice' => $request->boolean('has_ice', false),
            'is_active' => $request->boolean('is_active', true)
        ]);

        return redirect()->route('admin.tariff-codes')
            ->with('success', 'Código arancelario actualizado exitosamente.');
    }

    /**
     * Delete tariff code
     */
    public function destroyTariffCode(string $hsCode)
    {
        $tariffCode = TariffCode::where('hs_code', $hsCode)->firstOrFail();
        
        try {
            $tariffCode->delete();
            return redirect()->route('admin.tariff-codes')
                ->with('success', 'Código arancelario eliminado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->route('admin.tariff-codes')
                ->with('error', 'No se puede eliminar el código arancelario. Puede estar siendo utilizado en cálculos existentes.');
        }
    }

    /**
     * Display TLC schedules management
     */
    public function tlcSchedules(Request $request): View
    {
        $query = TlcSchedule::query()->with('tariffCode');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('hs_code', 'like', "%{$search}%");
        }

        if ($request->filled('country')) {
            $query->where('country_code', $request->get('country'));
        }

        if ($request->filled('reduction_type')) {
            $query->where('reduction_type', $request->get('reduction_type'));
        }

        $tlcSchedules = $query->orderBy('hs_code')
                            ->orderBy('country_code')
                            ->paginate(50);

        $countries = TlcSchedule::distinct()->pluck('country_code');
        $reductionTypes = ['immediate', 'linear', 'staged'];

        return view('admin.tlc-schedules', compact('tlcSchedules', 'countries', 'reductionTypes'));
    }

    public function massUpdateIvaRate(Request $request)
    {
        $request->validate([
            'new_iva_rate' => 'required|numeric|min:0|max:100',
            'old_iva_rate' => 'nullable|numeric|min:0|max:100'
        ]);

        $query = TariffCode::query();
        
        if ($request->old_iva_rate !== null) {
            $query->where('iva_rate', $request->old_iva_rate);
        }
        
        $updated = $query->update(['iva_rate' => $request->new_iva_rate]);
        
        return redirect()->back()->with('success', "Actualizado IVA en {$updated} códigos arancelarios.");
    }

    public function localExpensesConfig()
    {
        $preTaxDefaults = SystemSetting::get('default_additional_costs_pre_tax', []);
        $postTaxDefaults = SystemSetting::get('default_additional_costs_post_tax', []);
        
        return view('admin.local-expenses-config', compact('preTaxDefaults', 'postTaxDefaults'));
    }

    public function updateLocalExpensesConfig(Request $request)
    {
        $request->validate([
            'pre_tax_costs' => 'required|array',
            'post_tax_costs' => 'required|array',
        ]);

        SystemSetting::set('default_additional_costs_pre_tax', $request->pre_tax_costs);
        SystemSetting::set('default_additional_costs_post_tax', $request->post_tax_costs);

        return redirect()->back()->with('success', 'Configuración de gastos locales actualizada.');
    }
}
