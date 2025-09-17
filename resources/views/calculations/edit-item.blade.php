@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    Editar Item
                </div>
                <div class="card-body">
                    <form action="{{ route('calculation-items.update', $calculationItem) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Part Number</label>
                                    <input type="text" class="form-control" name="part_number" value="{{ old('part_number', $calculationItem->part_number) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Descripción en Inglés</label>
                                    <input type="text" class="form-control" name="description_en" value="{{ old('description_en', $calculationItem->description_en) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Descripción en Español</label>
                                    <input type="text" class="form-control" name="description_es" value="{{ old('description_es', $calculationItem->description_es) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Código Arancelario (HS)</label>
                                    <input type="text" class="form-control" name="hs_code" value="{{ old('hs_code', $calculationItem->hs_code) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" name="quantity" value="{{ old('quantity', $calculationItem->quantity) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Precio Unitario FOB (USD)</label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" name="unit_price_fob" value="{{ old('unit_price_fob', $calculationItem->unit_price_fob) }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label class="form-label">Peso Unitario (Kg)</label>
                                    <input type="number" step="0.01" min="0" class="form-control" name="unit_weight" value="{{ old('unit_weight', $calculationItem->unit_weight) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Exención ICE</label>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="ice_exempt" value="1" {{ old('ice_exempt', $calculationItem->ice_exempt) ? 'checked' : '' }}>
                                        <label class="form-check-label">Exento de ICE</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Razón de Exención ICE</label>
                                    <input type="text" class="form-control" name="ice_exempt_reason" value="{{ old('ice_exempt_reason', $calculationItem->ice_exempt_reason) }}">
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('calculations.show', $calculationItem->calculation) }}" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Actualizar Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
