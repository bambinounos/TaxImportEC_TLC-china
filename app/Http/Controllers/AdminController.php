<?php

namespace App\Http\Controllers;

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
}
