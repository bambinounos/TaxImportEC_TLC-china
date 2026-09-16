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
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Exención ICE</label>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="ice_exempt" value="1" {{ old('ice_exempt', $calculationItem->ice_exempt) ? 'checked' : '' }}>
                                        <label class="form-check-label">Exento de ICE</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Razón de Exención ICE</label>
                                    <input type="text" class="form-control" name="ice_exempt_reason" value="{{ old('ice_exempt_reason', $calculationItem->ice_exempt_reason) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Margen de Ganancia Individual</label>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="use_custom_profit" id="use_custom_profit" value="1"
                                            {{ old('use_custom_profit', $calculationItem->profit_margin_percent !== null ? '1' : '') ? 'checked' : '' }}
                                            onchange="document.getElementById('custom_profit_input').disabled = !this.checked">
                                        <label class="form-check-label" for="use_custom_profit">Usar margen individual</label>
                                    </div>
                                    <input type="number" step="0.01" min="0" max="1000" class="form-control" name="profit_margin_percent" id="custom_profit_input"
                                        value="{{ old('profit_margin_percent', $calculationItem->profit_margin_percent) }}"
                                        placeholder="Margen global: {{ number_format($calculationItem->calculation->profit_margin_percent, 2) }}%"
                                        {{ $calculationItem->profit_margin_percent === null ? 'disabled' : '' }}>
                                    <div class="form-text">Si no se marca, se usará el margen global del cálculo.</div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-2">
                            <h6 class="text-primary mb-3"><i class="fas fa-file-contract"></i> Exoneraciones SENAE / Código Liberatorio (TPNG)</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Código Liberatorio / TPNG</label>
                                        <select class="form-select" id="edit_liberation_select">
                                            <option value="">-- Sin exoneración especial (Tarifa General) --</option>
                                            @if(isset($liberations))
                                                @foreach($liberations as $lib)
                                                    <option value="{{ $lib->code }}"
                                                        data-exempts-iva="{{ $lib->exempts_iva ? '1' : '0' }}"
                                                        data-exempts-tariff="{{ $lib->exempts_tariff ? '1' : '0' }}"
                                                        data-legal-basis="{{ $lib->legal_basis ?? $lib->description }}"
                                                        {{ old('liberation_code', $calculationItem->liberation_code) == $lib->code ? 'selected' : '' }}>
                                                        {{ $lib->code }} - {{ $lib->description }} ({{ $lib->type }})
                                                    </option>
                                                @endforeach
                                            @endif
                                            <option value="__custom__" {{ old('liberation_code', $calculationItem->liberation_code) && (isset($liberations) && !$liberations->contains('code', old('liberation_code', $calculationItem->liberation_code))) ? 'selected' : '' }}>-- Otro código manual --</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6" id="edit_custom_liberation_div" style="{{ old('liberation_code', $calculationItem->liberation_code) && (isset($liberations) && !$liberations->contains('code', old('liberation_code', $calculationItem->liberation_code))) ? '' : 'display: none;' }}">
                                    <div class="mb-3">
                                        <label class="form-label">Código TPNG Manual</label>
                                        <input type="text" class="form-control" id="edit_custom_liberation_input" placeholder="Ej: 0672" value="{{ old('liberation_code', $calculationItem->liberation_code) }}">
                                    </div>
                                </div>
                            </div>

                            <input type="hidden" name="liberation_code" id="edit_liberation_code" value="{{ old('liberation_code', $calculationItem->liberation_code) }}">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Exención IVA</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="iva_exempt" id="edit_iva_exempt" value="1" {{ old('iva_exempt', $calculationItem->iva_exempt) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="edit_iva_exempt">Exento de IVA (Tarifa 0%)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Razón / Base Legal IVA</label>
                                        <input type="text" class="form-control" name="iva_exempt_reason" id="edit_iva_exempt_reason" value="{{ old('iva_exempt_reason', $calculationItem->iva_exempt_reason) }}" placeholder="Ej: LORTI Art. 55 Num. 5">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Exención Arancelaria</label>
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" name="tariff_exempt" id="edit_tariff_exempt" value="1" {{ old('tariff_exempt', $calculationItem->tariff_exempt) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="edit_tariff_exempt">Exento de Arancel Ad-Valorem (0%)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Razón Exención Arancel</label>
                                        <input type="text" class="form-control" name="tariff_exempt_reason" id="edit_tariff_exempt_reason" value="{{ old('tariff_exempt_reason', $calculationItem->tariff_exempt_reason) }}" placeholder="Ej: Exoneración COPCI Art. 125">
                                    </div>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const libSelect = document.getElementById('edit_liberation_select');
    const customDiv = document.getElementById('edit_custom_liberation_div');
    const customInput = document.getElementById('edit_custom_liberation_input');
    const hiddenCode = document.getElementById('edit_liberation_code');
    const ivaCheck = document.getElementById('edit_iva_exempt');
    const ivaReason = document.getElementById('edit_iva_exempt_reason');
    const tariffCheck = document.getElementById('edit_tariff_exempt');
    const tariffReason = document.getElementById('edit_tariff_exempt_reason');

    if (libSelect) {
        libSelect.addEventListener('change', function () {
            const val = this.value;
            if (val === '__custom__') {
                if (customDiv) customDiv.style.display = 'block';
                if (hiddenCode && customInput) hiddenCode.value = customInput.value.trim();
            } else if (val === '') {
                if (customDiv) customDiv.style.display = 'none';
                if (hiddenCode) hiddenCode.value = '';
            } else {
                if (customDiv) customDiv.style.display = 'none';
                if (hiddenCode) hiddenCode.value = val;
                const opt = this.options[this.selectedIndex];
                if (opt && opt.dataset) {
                    if (opt.dataset.exemptsIva === '1') {
                        if (ivaCheck) ivaCheck.checked = true;
                        if (ivaReason && !ivaReason.value.trim()) {
                            ivaReason.value = opt.dataset.legalBasis || ('Liberación SENAE ' + val);
                        }
                    }
                    if (opt.dataset.exemptsTariff === '1') {
                        if (tariffCheck) tariffCheck.checked = true;
                        if (tariffReason && !tariffReason.value.trim()) {
                            tariffReason.value = opt.dataset.legalBasis || ('Liberación SENAE ' + val);
                        }
                    }
                }
            }
        });

        if (customInput) {
            customInput.addEventListener('input', function () {
                if (libSelect.value === '__custom__' && hiddenCode) {
                    hiddenCode.value = this.value.trim();
                }
            });
        }
    }
});
</script>
@endpush
