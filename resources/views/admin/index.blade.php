@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <h1><i class="fas fa-cog"></i> Panel de Administración</h1>
            <p class="text-muted">Gestión de partidas arancelarias y configuraciones del sistema</p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['total_tariff_codes']) }}</h4>
                            <p class="mb-0">Partidas Arancelarias</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-list-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['active_tariff_codes']) }}</h4>
                            <p class="mb-0">Partidas Activas</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-check-circle fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['total_tlc_schedules']) }}</h4>
                            <p class="mb-0">Cronogramas TLC</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h4>{{ number_format($stats['active_tlc_schedules']) }}</h4>
                            <p class="mb-0">TLC Activos</p>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-handshake fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-list-alt"></i> Gestión de Partidas Arancelarias</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Administrar las {{ number_format($stats['total_tariff_codes']) }} partidas arancelarias del sistema, incluyendo códigos HS, descripciones, tarifas base y configuraciones ICE.</p>
                    <a href="{{ route('admin.tariff-codes') }}" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Administrar Partidas
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Cronogramas TLC China</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">Gestionar los {{ number_format($stats['total_tlc_schedules']) }} cronogramas de reducción arancelaria del Tratado de Libre Comercio con China.</p>
                    <a href="{{ route('admin.tlc-schedules') }}" class="btn btn-info">
                        <i class="fas fa-handshake"></i> Ver Cronogramas TLC
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Base de Datos</h6>
                            <ul class="list-unstyled">
                                <li><strong>Partidas Arancelarias:</strong> {{ number_format($stats['total_tariff_codes']) }} códigos HS completos</li>
                                <li><strong>Cronogramas TLC:</strong> {{ number_format($stats['total_tlc_schedules']) }} entradas de reducción</li>
                                <li><strong>Cobertura:</strong> Todos los códigos arancelarios de Ecuador</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6>Funcionalidades</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Cálculo automático de impuestos</li>
                                <li><i class="fas fa-check text-success"></i> Aplicación de TLC China</li>
                                <li><i class="fas fa-check text-success"></i> Exoneración ICE por producto</li>
                                <li><i class="fas fa-check text-success"></i> Importación/Exportación CSV</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
