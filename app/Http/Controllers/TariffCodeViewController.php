<?php

namespace App\Http\Controllers;

use App\Models\TariffCode;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TariffCodeViewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->canViewTariffs()) {
                abort(403, 'Acceso denegado. No tiene permisos para ver partidas arancelarias.');
            }
            return $next($request);
        });
    }

    public function index(Request $request): View
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

        if ($request->filled('has_ice')) {
            $query->where('has_ice', $request->get('has_ice') === 'yes');
        }

        $tariffCodes = $query->where('is_active', true)
                           ->with('tlcSchedules')
                           ->orderBy('hierarchy_level')
                           ->orderBy('hs_code')
                           ->paginate(50);

        return view('tariff-codes.index', compact('tariffCodes'));
    }

    public function show(string $hsCode): View
    {
        $tariffCode = TariffCode::where('hs_code', $hsCode)
                               ->where('is_active', true)
                               ->with(['tlcSchedules' => function ($query) {
                                   $query->orderBy('country_code')->orderBy('start_date');
                               }])
                               ->firstOrFail();

        return view('tariff-codes.show', compact('tariffCode'));
    }
}
