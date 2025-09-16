@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-handshake"></i> Cronogramas TLC China</h1>
                    <p class="text-muted">Gestión de {{ $tlcSchedules->total() }} cronogramas de reducción arancelaria</p>
                </div>
                <a href="{{ route('admin.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Panel
                </a>
            </div>
        </div>
    </div>

    <!-- Search and Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.tlc-schedules') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label for="search" class="form-label">Código HS</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Buscar por código HS...">
                            </div>
                            <div class="col-md-2">
                                <label for="country" class="form-label">País</label>
                                <select class="form-select" id="country" name="country">
                                    <option value="">Todos</option>
                                    @foreach($countries as $country)
                                        <option value="{{ $country }}" {{ request('country') === $country ? 'selected' : '' }}>
                                            {{ $country === 'CHN' ? 'China' : $country }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="reduction_type" class="form-label">Tipo de Reducción</label>
                                <select class="form-select" id="reduction_type" name="reduction_type">
                                    <option value="">Todos</option>
                                    @foreach($reductionTypes as $type)
                                        <option value="{{ $type }}" {{ request('reduction_type') === $type ? 'selected' : '' }}>
                                            {{ ucfirst($type) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="{{ route('admin.tlc-schedules') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cronogramas TLC</h5>
                    <span class="badge bg-primary">{{ $tlcSchedules->total() }} resultados</span>
                </div>
                <div class="card-body p-0">
                    @if($tlcSchedules->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código HS</th>
                                        <th>Descripción</th>
                                        <th>País</th>
                                        <th>Tarifa Base</th>
                                        <th>Tipo Reducción</th>
                                        <th>Años Eliminación</th>
                                        <th>Fecha Inicio</th>
                                        <th>Tarifa Actual</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tlcSchedules as $schedule)
                                    <tr>
                                        <td>
                                            <code class="text-primary">{{ $schedule->hs_code }}</code>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" 
                                                 title="{{ $schedule->tariffCode->description_es ?? 'N/A' }}">
                                                {{ $schedule->tariffCode->description_es ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">
                                                {{ $schedule->country_code === 'CHN' ? 'China' : $schedule->country_code }}
                                            </span>
                                        </td>
                                        <td>{{ number_format($schedule->base_rate, 2) }}%</td>
                                        <td>
                                            <span class="badge bg-{{ $schedule->reduction_type === 'immediate' ? 'success' : ($schedule->reduction_type === 'linear' ? 'info' : 'warning') }}">
                                                {{ ucfirst($schedule->reduction_type) }}
                                            </span>
                                        </td>
                                        <td>{{ $schedule->elimination_years }} años</td>
                                        <td>{{ $schedule->start_date->format('d/m/Y') }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ number_format($schedule->getEffectiveRate(date('Y')), 2) }}%
                                            </span>
                                        </td>
                                        <td>
                                            @if($schedule->is_active)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.tariff-codes.show', $schedule->hs_code) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Ver partida completa">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="card-footer">
                            {{ $tlcSchedules->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5>No se encontraron cronogramas TLC</h5>
                            <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
