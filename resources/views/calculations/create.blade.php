@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4>Nuevo Cálculo de Impuestos</h4>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('calculations.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nombre del Cálculo *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="calculation_year" class="form-label">Año de Cálculo *</label>
                                    <input type="number" class="form-control @error('calculation_year') is-invalid @enderror" 
                                           id="calculation_year" name="calculation_year" 
                                           value="{{ old('calculation_year', date('Y')) }}" 
                                           min="2024" max="2050" required>
                                    @error('calculation_year')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="use_tlc_china" 
                                               name="use_tlc_china" value="1" {{ old('use_tlc_china') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="use_tlc_china">
                                            Usar TLC China
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Aplicar reducciones arancelarias del Tratado de Libre Comercio con China
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="proration_method" class="form-label">Método de Prorrateo *</label>
                                    <select class="form-select @error('proration_method') is-invalid @enderror" 
                                            id="proration_method" name="proration_method" required>
                                        <option value="weight" {{ old('proration_method') == 'weight' ? 'selected' : '' }}>
                                            Por Peso
                                        </option>
                                        <option value="price" {{ old('proration_method') == 'price' ? 'selected' : '' }}>
                                            Por Precio
                                        </option>
                                    </select>
                                    @error('proration_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="freight_cost" class="form-label">Costo de Flete (USD) *</label>
                                    <input type="number" step="0.01" min="0" 
                                           class="form-control @error('freight_cost') is-invalid @enderror" 
                                           id="freight_cost" name="freight_cost" 
                                           value="{{ old('freight_cost', '0.00') }}" required>
                                    @error('freight_cost')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="insurance_rate" class="form-label">Tasa de Seguro (%) *</label>
                                    <input type="number" step="0.01" min="0" max="100" 
                                           class="form-control @error('insurance_rate') is-invalid @enderror" 
                                           id="insurance_rate" name="insurance_rate" 
                                           value="{{ old('insurance_rate', '1.00') }}" required>
                                    @error('insurance_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="profit_margin_percent" class="form-label">Margen de Ganancia (%) *</label>
                                    <input type="number" step="0.01" min="0" max="1000" 
                                           class="form-control @error('profit_margin_percent') is-invalid @enderror" 
                                           id="profit_margin_percent" name="profit_margin_percent" 
                                           value="{{ old('profit_margin_percent', '60.00') }}" required>
                                    @error('profit_margin_percent')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        @include('calculations.partials.local-expenses', [
                            'defaultPreTaxCosts' => \App\Models\SystemSetting::get('default_additional_costs_pre_tax', []),
                            'defaultPostTaxCosts' => \App\Models\SystemSetting::get('default_additional_costs_post_tax', [])
                        ])

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('calculations.index') }}" class="btn btn-secondary me-md-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Crear Cálculo</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
