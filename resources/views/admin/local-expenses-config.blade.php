@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4>Configuración de Gastos Locales</h4>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Mass IVA Update Section -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Actualización Masiva de IVA</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('admin.mass-update-iva') }}">
                                @csrf
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="old_iva_rate" class="form-label">IVA Actual (%) - Opcional</label>
                                            <input type="number" step="0.01" min="0" max="100" 
                                                   class="form-control" id="old_iva_rate" name="old_iva_rate" 
                                                   placeholder="Dejar vacío para actualizar todos">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="new_iva_rate" class="form-label">Nuevo IVA (%) *</label>
                                            <input type="number" step="0.01" min="0" max="100" 
                                                   class="form-control" id="new_iva_rate" name="new_iva_rate" 
                                                   value="15" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-warning d-block">
                                                Actualizar IVA Masivamente
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Local Expenses Configuration -->
                    <form method="POST" action="{{ route('admin.local-expenses-config.update') }}">
                        @csrf

                        <!-- Pre-Tax Costs Configuration -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Costos Pre-Impuestos por Defecto (0% IVA)</h5>
                            </div>
                            <div class="card-body">
                                <div id="pre-tax-config">
                                    @if($preTaxDefaults)
                                        @foreach($preTaxDefaults as $name => $amount)
                                            <div class="row mb-2">
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control" 
                                                           name="pre_tax_costs[{{ $name }}]" 
                                                           value="{{ $name }}" readonly>
                                                </div>
                                                <div class="col-md-4">
                                                    <input type="number" step="0.01" min="0" class="form-control" 
                                                           name="pre_tax_amounts[{{ $name }}]" 
                                                           value="{{ $amount }}" placeholder="Monto USD">
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="this.closest('.row').remove()">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addPreTaxConfig()">
                                    <i class="fas fa-plus"></i> Agregar Costo Pre-Impuesto
                                </button>
                            </div>
                        </div>

                        <!-- Post-Tax Costs Configuration -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5>Costos Post-Impuestos por Defecto</h5>
                            </div>
                            <div class="card-body">
                                <div id="post-tax-config">
                                    @if($postTaxDefaults)
                                        @foreach($postTaxDefaults as $name => $cost)
                                            <div class="row mb-2">
                                                <div class="col-md-5">
                                                    <input type="text" class="form-control" 
                                                           name="post_tax_costs[{{ $name }}][name]" 
                                                           value="{{ $name }}" readonly>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="number" step="0.01" min="0" class="form-control" 
                                                           name="post_tax_costs[{{ $name }}][amount]" 
                                                           value="{{ is_array($cost) ? $cost['amount'] : $cost }}" 
                                                           placeholder="Monto USD">
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" 
                                                               name="post_tax_costs[{{ $name }}][iva_applies]" 
                                                               value="1" {{ (is_array($cost) && ($cost['iva_applies'] ?? false)) ? 'checked' : '' }}>
                                                        <label class="form-check-label">IVA 15%</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                                            onclick="this.closest('.row').remove()">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="addPostTaxConfig()">
                                    <i class="fas fa-plus"></i> Agregar Costo Post-Impuesto
                                </button>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.index') }}" class="btn btn-secondary me-md-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar Configuración</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let preTaxConfigIndex = {{ count($preTaxDefaults ?? []) }};
let postTaxConfigIndex = {{ count($postTaxDefaults ?? []) }};

function addPreTaxConfig() {
    const html = `
        <div class="row mb-2">
            <div class="col-md-6">
                <input type="text" class="form-control" 
                       name="pre_tax_costs[new_${preTaxConfigIndex}]" 
                       placeholder="Nombre del costo" required>
            </div>
            <div class="col-md-4">
                <input type="number" step="0.01" min="0" class="form-control" 
                       name="pre_tax_amounts[new_${preTaxConfigIndex}]" 
                       placeholder="Monto USD" value="0">
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="this.closest('.row').remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('pre-tax-config').insertAdjacentHTML('beforeend', html);
    preTaxConfigIndex++;
}

function addPostTaxConfig() {
    const html = `
        <div class="row mb-2">
            <div class="col-md-5">
                <input type="text" class="form-control" 
                       name="post_tax_costs[new_${postTaxConfigIndex}][name]" 
                       placeholder="Nombre del costo" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" min="0" class="form-control" 
                       name="post_tax_costs[new_${postTaxConfigIndex}][amount]" 
                       placeholder="Monto USD" value="0">
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" 
                           name="post_tax_costs[new_${postTaxConfigIndex}][iva_applies]" 
                           value="1">
                    <label class="form-check-label">IVA 15%</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" 
                        onclick="this.closest('.row').remove()">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('post-tax-config').insertAdjacentHTML('beforeend', html);
    postTaxConfigIndex++;
}
</script>
@endsection
