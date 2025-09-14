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
        </div>
    </div>
</div>
@endsection
