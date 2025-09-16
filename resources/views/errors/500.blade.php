@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-danger text-white">
                    <h4><i class="fas fa-exclamation-triangle"></i> Error del Servidor (500)</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-server fa-4x text-danger mb-3"></i>
                        <h5>Lo sentimos, ha ocurrido un error interno del servidor</h5>
                        <p class="text-muted">Nuestro equipo técnico ha sido notificado del problema.</p>
                    </div>
                    
                    @if(config('app.debug') && isset($exception))
                        <div class="alert alert-warning">
                            <h6>Información de Debug:</h6>
                            <strong>Error:</strong> {{ $exception->getMessage() }}<br>
                            <strong>Archivo:</strong> {{ $exception->getFile() }}<br>
                            <strong>Línea:</strong> {{ $exception->getLine() }}
                        </div>
                    @endif
                    
                    <div class="text-center">
                        <a href="{{ route('dashboard') }}" class="btn btn-primary">
                            <i class="fas fa-home"></i> Volver al Dashboard
                        </a>
                        <a href="{{ route('calculations.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-calculator"></i> Mis Cálculos
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
