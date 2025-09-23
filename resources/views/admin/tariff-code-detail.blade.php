@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-list-alt"></i> Partida Arancelaria: {{ $tariffCode->hs_code }}</h1>
                    <p class="text-muted">Detalles completos y cronogramas TLC</p>
                </div>
                <a href="{{ route('admin.tariff-codes') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Partidas
                </a>
            </div>
        </div>
    </div>

    <!-- Basic Information -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información Básica</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Código HS:</th>
                                    <td><code class="text-primary fs-5">{{ $tariffCode->hs_code }}</code></td>
                                </tr>
                                <tr>
                                    <th>Descripción (ES):</th>
                                    <td>{{ $tariffCode->description_es }}</td>
                                </tr>
                                <tr>
                                    <th>Descripción (EN):</th>
                                    <td>{{ $tariffCode->description_en }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="30%">Tarifa Base:</th>
                                    <td><span class="badge bg-primary fs-6">{{ number_format($tariffCode->base_tariff_rate, 2) }}%</span></td>
                                </tr>
                                <tr>
                                    <th>IVA:</th>
                                    <td><span class="badge bg-info fs-6">{{ number_format($tariffCode->iva_rate, 2) }}%</span></td>
                                </tr>
                                <tr>
                                    <th>FODINFA:</th>
                                    <td><span class="badge bg-info fs-6">0.5%</span> (Fijo para todos los productos)</td>
                                </tr>
                                <tr>
                                    <th>ICE:</th>
                                    <td>
                                        @if($tariffCode->has_ice)
                                            <span class="badge bg-warning fs-6">Aplica ICE</span>
                                        @else
                                            <span class="badge bg-secondary fs-6">Sin ICE</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Estado:</th>
                                    <td>
                                        @if($tariffCode->is_active)
                                            <span class="badge bg-success fs-6">Activo</span>
                                        @else
                                            <span class="badge bg-danger fs-6">Inactivo</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- TLC Schedules -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-handshake"></i> Cronogramas TLC 
                        <span class="badge bg-primary">{{ $tariffCode->tlcSchedules->count() }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    @if($tariffCode->tlcSchedules->count() > 0)
                        @foreach($tariffCode->tlcSchedules as $schedule)
                        <div class="card mb-3">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">
                                        <i class="fas fa-flag"></i> 
                                        {{ $schedule->country_code === 'CHN' ? 'China' : $schedule->country_code }}
                                    </h6>
                                    <div>
                                        <span class="badge bg-{{ $schedule->reduction_type === 'immediate' ? 'success' : ($schedule->reduction_type === 'linear' ? 'info' : 'warning') }}">
                                            {{ ucfirst($schedule->reduction_type) }}
                                        </span>
                                        @if($schedule->is_active)
                                            <span class="badge bg-success">Activo</span>
                                        @else
                                            <span class="badge bg-secondary">Inactivo</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <strong>Tarifa Base:</strong><br>
                                        <span class="text-primary">{{ number_format($schedule->base_rate, 2) }}%</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Años de Eliminación:</strong><br>
                                        <span class="text-info">{{ $schedule->elimination_years }} años</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Fecha de Inicio:</strong><br>
                                        <span class="text-success">{{ $schedule->start_date->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <strong>Tarifa Actual ({{ date('Y') }}):</strong><br>
                                        <span class="badge bg-primary fs-6">{{ number_format($schedule->getEffectiveRate(date('Y')), 2) }}%</span>
                                    </div>
                                </div>

                                @if($schedule->yearly_rates && count($schedule->yearly_rates) > 0)
                                <div class="mt-3">
                                    <h6>Cronograma de Reducción:</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-striped">
                                            <thead>
                                                <tr>
                                                    <th>Año</th>
                                                    <th>Tarifa</th>
                                                    <th>Reducción</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $currentYear = date('Y');
                                                    $startYear = $schedule->start_date->year;
                                                @endphp
                                                @foreach($schedule->yearly_rates as $year => $rate)
                                                <tr class="{{ $year == $currentYear ? 'table-warning' : '' }}">
                                                    <td>
                                                        {{ $year }}
                                                        @if($year == $currentYear)
                                                            <span class="badge bg-warning text-dark">Actual</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($rate, 2) }}%</td>
                                                    <td>
                                                        @php
                                                            $reduction = $schedule->base_rate - $rate;
                                                            $reductionPercent = $schedule->base_rate > 0 ? ($reduction / $schedule->base_rate) * 100 : 0;
                                                        @endphp
                                                        -{{ number_format($reduction, 2) }}% 
                                                        <small class="text-muted">({{ number_format($reductionPercent, 1) }}%)</small>
                                                    </td>
                                                    <td>
                                                        @if($year < $currentYear)
                                                            <span class="badge bg-success">Aplicado</span>
                                                        @elseif($year == $currentYear)
                                                            <span class="badge bg-warning text-dark">Vigente</span>
                                                        @else
                                                            <span class="badge bg-secondary">Futuro</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                @endif

                                @if($schedule->notes)
                                <div class="mt-3">
                                    <h6>Notas:</h6>
                                    <p class="text-muted">{{ $schedule->notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-handshake fa-3x text-muted mb-3"></i>
                            <h5>Sin Cronogramas TLC</h5>
                            <p class="text-muted">Esta partida arancelaria no tiene cronogramas de reducción TLC configurados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
