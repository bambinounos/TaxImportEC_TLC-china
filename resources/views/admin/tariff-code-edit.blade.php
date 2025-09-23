@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4><i class="fas fa-edit"></i> Editar Código Arancelario: {{ $tariffCode->hs_code }}</h4>
                    <div>
                        <a href="{{ route('admin.tariff-code.show', $tariffCode) }}" class="btn btn-info me-2">
                            <i class="fas fa-eye"></i> Ver Detalle
                        </a>
                        <a href="{{ route('admin.tariff-codes') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver al Catálogo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.tariff-code.update', $tariffCode) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="hs_code" class="form-label">Código HS <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('hs_code') is-invalid @enderror" 
                                           id="hs_code" name="hs_code" value="{{ old('hs_code', $tariffCode->hs_code) }}" 
                                           placeholder="Ej: 1234567890" maxlength="20" required>
                                    @error('hs_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group mb-3">
                                    <label for="base_tariff_rate" class="form-label">Arancel Base (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('base_tariff_rate') is-invalid @enderror" 
                                           id="base_tariff_rate" name="base_tariff_rate" value="{{ old('base_tariff_rate', $tariffCode->base_tariff_rate) }}" 
                                           step="0.01" min="0" max="100" required>
                                    @error('base_tariff_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label for="iva_rate" class="form-label">IVA (%) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('iva_rate') is-invalid @enderror" 
                                           id="iva_rate" name="iva_rate" value="{{ old('iva_rate', $tariffCode->iva_rate) }}" 
                                           step="0.01" min="0" max="100" required>
                                    @error('iva_rate')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-3">
                                    <label for="fodinfa_rate" class="form-label">FODINFA (%)</label>
                                    <input type="number" class="form-control" id="fodinfa_rate" name="fodinfa_rate" 
                                           value="{{ old('fodinfa_rate', $tariffCode->fodinfa_rate ?? 0.5) }}" 
                                           step="0.01" min="0" max="100" readonly>
                                    <div class="form-text">Tasa fija del 0.5% para todos los productos</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="unit" class="form-label">Unidad</label>
                                    <input type="text" class="form-control @error('unit') is-invalid @enderror" 
                                           id="unit" name="unit" value="{{ old('unit', $tariffCode->unit) }}" 
                                           placeholder="Ej: kg, u, m" maxlength="10">
                                    @error('unit')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label">Configuración</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="has_ice" name="has_ice" value="1" 
                                                   {{ old('has_ice', $tariffCode->has_ice) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="has_ice">
                                                Tiene ICE
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                                   {{ old('is_active', $tariffCode->is_active) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_active">
                                                Código Activo
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="description_en" class="form-label">Descripción en Inglés <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description_en') is-invalid @enderror" 
                                      id="description_en" name="description_en" rows="3" 
                                      maxlength="500" required>{{ old('description_en', $tariffCode->description_en) }}</textarea>
                            @error('description_en')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="description_es" class="form-label">Descripción en Español <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('description_es') is-invalid @enderror" 
                                      id="description_es" name="description_es" rows="3" 
                                      maxlength="500" required>{{ old('description_es', $tariffCode->description_es) }}</textarea>
                            @error('description_es')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>


                        <div class="d-flex justify-content-between">
                            <div>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle"></i> 
                                    Creado: {{ $tariffCode->created_at->format('d/m/Y H:i') }} | 
                                    Actualizado: {{ $tariffCode->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                            <div>
                                <a href="{{ route('admin.tariff-codes') }}" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Actualizar Código Arancelario
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
