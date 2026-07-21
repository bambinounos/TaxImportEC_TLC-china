@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4>Mis Cálculos</h4>
                    <a href="{{ route('calculations.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Cálculo
                    </a>
                </div>

                <div class="card-body">
                    <form method="GET" action="{{ route('calculations.index') }}" class="mb-3">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" value="{{ $search }}"
                                   placeholder="Buscar por nombre, descripción o producto (part number, descripción, partida HS)..."
                                   aria-label="Buscar cálculos">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i> Buscar
                            </button>
                            @if($search !== '')
                                <a href="{{ route('calculations.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                            @endif
                        </div>
                    </form>

                    @if($search !== '' && $calculations->total() > 0)
                        <p class="text-muted small mb-3">
                            {{ $calculations->total() }} {{ $calculations->total() === 1 ? 'cálculo encontrado' : 'cálculos encontrados' }} para «{{ $search }}»
                        </p>
                    @endif

                    @if($calculations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>TLC China</th>
                                        <th>Año</th>
                                        <th>Items</th>
                                        <th>Total FOB</th>
                                        <th>Total Impuestos</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($calculations as $calculation)
                                    <tr>
                                        <td>
                                            <a href="{{ route('calculations.show', $calculation) }}" class="text-decoration-none">
                                                {{ $calculation->name }}
                                            </a>
                                            @if($calculation->shares->count() > 0)
                                                <br><small class="text-info"><i class="fas fa-share-alt"></i> Compartido ({{ $calculation->shares->count() }})</small>
                                            @endif
                                        </td>
                                        <td>{{ Str::limit($calculation->description, 50) }}</td>
                                        <td>
                                            @if($calculation->use_tlc_china)
                                                <span class="badge bg-success">Sí</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>{{ $calculation->calculation_year }}</td>
                                        <td>{{ $calculation->items->count() }}</td>
                                        <td>${{ number_format($calculation->total_fob_value, 2) }}</td>
                                        <td>${{ number_format($calculation->total_taxes, 2) }}</td>
                                        <td>{{ $calculation->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('calculations.show', $calculation) }}"
                                                   class="btn btn-sm btn-outline-primary">Ver</a>
                                                <form method="POST" action="{{ route('calculations.destroy', $calculation) }}"
                                                      class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este cálculo?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{ $calculations->links() }}
                    @elseif($search !== '')
                        <div class="text-center py-4">
                            <h5>No se encontraron cálculos para «{{ $search }}»</h5>
                            <p class="text-muted">Pruebe con otro nombre, part number o partida arancelaria.</p>
                            <a href="{{ route('calculations.index') }}" class="btn btn-outline-secondary">Ver todos los cálculos</a>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <h5>No hay cálculos creados</h5>
                            <p class="text-muted">Comience creando su primer cálculo de impuestos de importación.</p>
                            <a href="{{ route('calculations.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Crear Primer Cálculo
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Shared Calculations Section --}}
            @if($sharedCalculations->count() > 0)
            <div class="card mt-4">
                <div class="card-header">
                    <h4><i class="fas fa-share-alt"></i> Compartidos Conmigo</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Propietario</th>
                                    <th>Permiso</th>
                                    <th>TLC China</th>
                                    <th>Año</th>
                                    <th>Total FOB</th>
                                    <th>Compartido el</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sharedCalculations as $calculation)
                                <tr>
                                    <td>
                                        <a href="{{ route('calculations.show', $calculation) }}" class="text-decoration-none">
                                            {{ $calculation->name }}
                                        </a>
                                    </td>
                                    <td>{{ $calculation->user->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($calculation->pivot->permission === 'edit')
                                            <span class="badge bg-success">Editar</span>
                                        @else
                                            <span class="badge bg-info">Solo ver</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($calculation->use_tlc_china)
                                            <span class="badge bg-success">Sí</span>
                                        @else
                                            <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $calculation->calculation_year }}</td>
                                    <td>${{ number_format($calculation->total_fob_value, 2) }}</td>
                                    <td>{{ $calculation->pivot->created_at ? \Carbon\Carbon::parse($calculation->pivot->created_at)->format('d/m/Y H:i') : 'N/A' }}</td>
                                    <td>
                                        <a href="{{ route('calculations.show', $calculation) }}"
                                           class="btn btn-sm btn-outline-primary">Ver</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $sharedCalculations->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
