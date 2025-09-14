@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1>Dashboard</h1>
            <p class="text-muted">Bienvenido al sistema de cálculo de impuestos de importación</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-plus-circle text-primary"></i>
                        Nuevo Cálculo
                    </h5>
                    <p class="card-text">Crear un nuevo cálculo de impuestos de importación</p>
                    <a href="{{ route('calculations.create') }}" class="btn btn-primary">Crear</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-list text-info"></i>
                        Mis Cálculos
                    </h5>
                    <p class="card-text">Ver y gestionar cálculos existentes</p>
                    <a href="{{ route('calculations.index') }}" class="btn btn-outline-primary">Ver Todos</a>
                </div>
            </div>
        </div>
        @if(auth()->user()->isAdmin())
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">
                        <i class="fas fa-cog text-secondary"></i>
                        Administración
                    </h5>
                    <p class="card-text">Gestionar partidas arancelarias y configuraciones</p>
                    <a href="/admin" class="btn btn-outline-secondary">Administrar</a>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Cálculos Recientes</h5>
                </div>
                <div class="card-body">
                    @php
                        $recentCalculations = auth()->user()->calculations()->latest()->take(5)->get();
                    @endphp
                    
                    @if($recentCalculations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Nombre</th>
                                        <th>TLC China</th>
                                        <th>Items</th>
                                        <th>Total FOB</th>
                                        <th>Fecha</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentCalculations as $calculation)
                                    <tr>
                                        <td>{{ $calculation->name }}</td>
                                        <td>
                                            @if($calculation->use_tlc_china)
                                                <span class="badge bg-success">Sí</span>
                                            @else
                                                <span class="badge bg-secondary">No</span>
                                            @endif
                                        </td>
                                        <td>{{ $calculation->items->count() }}</td>
                                        <td>${{ number_format($calculation->total_fob_value, 2) }}</td>
                                        <td>{{ $calculation->created_at->format('d/m/Y') }}</td>
                                        <td>
                                            <a href="{{ route('calculations.show', $calculation) }}" 
                                               class="btn btn-sm btn-outline-primary">Ver</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('calculations.index') }}" class="btn btn-sm btn-link">Ver todos los cálculos</a>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <p class="text-muted">No hay cálculos creados aún.</p>
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
