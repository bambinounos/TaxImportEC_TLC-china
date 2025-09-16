@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-list-alt"></i> Administrar Partidas Arancelarias</h1>
                    <p class="text-muted">Gestión de {{ $tariffCodes->total() }} partidas arancelarias</p>
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
                    <form method="GET" action="{{ route('admin.tariff-codes') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="search" name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Código HS, descripción...">
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Estado</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="has_ice" class="form-label">ICE</label>
                                <select class="form-select" id="has_ice" name="has_ice">
                                    <option value="">Todos</option>
                                    <option value="yes" {{ request('has_ice') === 'yes' ? 'selected' : '' }}>Con ICE</option>
                                    <option value="no" {{ request('has_ice') === 'no' ? 'selected' : '' }}>Sin ICE</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <a href="{{ route('admin.tariff-codes') }}" class="btn btn-outline-secondary">
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
                    <h5 class="mb-0">Partidas Arancelarias</h5>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary">{{ $tariffCodes->total() }} resultados</span>
                        <a href="{{ route('admin.tariff-codes.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus"></i> Nuevo Código
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($tariffCodes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Código HS</th>
                                        <th>Descripción (ES)</th>
                                        <th>Descripción (EN)</th>
                                        <th>Tarifa Base</th>
                                        <th>IVA</th>
                                        <th>ICE</th>
                                        <th>TLC</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tariffCodes as $tariffCode)
                                    <tr>
                                        <td>
                                            <code class="text-primary">{{ $tariffCode->hs_code }}</code>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $tariffCode->description_es }}">
                                                {{ $tariffCode->description_es }}
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 200px;" title="{{ $tariffCode->description_en }}">
                                                {{ $tariffCode->description_en }}
                                            </div>
                                        </td>
                                        <td>{{ number_format($tariffCode->base_tariff_rate, 2) }}%</td>
                                        <td>{{ number_format($tariffCode->iva_rate, 2) }}%</td>
                                        <td>
                                            @if($tariffCode->has_ice)
                                                <span class="badge bg-warning">Sí</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tariffCode->tlcSchedules->count() > 0)
                                                <span class="badge bg-success">{{ $tariffCode->tlcSchedules->count() }}</span>
                                            @else
                                                <span class="badge bg-secondary">0</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($tariffCode->is_active)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-danger">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.tariff-codes.show', $tariffCode->hs_code) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.tariff-codes.edit', $tariffCode->hs_code) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.tariff-codes.destroy', $tariffCode->hs_code) }}" 
                                                      method="POST" style="display: inline;" 
                                                      onsubmit="return confirm('¿Está seguro de eliminar este código arancelario?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="card-footer">
                            {{ $tariffCodes->withQueryString()->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-search fa-3x text-muted mb-3"></i>
                            <h5>No se encontraron partidas arancelarias</h5>
                            <p class="text-muted">Intenta ajustar los filtros de búsqueda</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
