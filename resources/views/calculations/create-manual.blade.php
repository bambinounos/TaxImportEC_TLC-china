@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1><i class="fas fa-edit"></i> Entrada Manual de Productos</h1>
                <a href="{{ route('calculations.create') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Nuevo Cálculo Manual</h5>
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

                    <form action="{{ route('calculations.store-manual') }}" method="POST" id="manualCalculationForm">
                        @csrf
                        
                        <div class="mb-4">
                            <label for="calculation_name" class="form-label">Nombre del Cálculo</label>
                            <input type="text" class="form-control" id="calculation_name" name="calculation_name" 
                                   value="{{ old('calculation_name') }}" required>
                        </div>

                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6>Productos</h6>
                                <button type="button" class="btn btn-success btn-sm" onclick="addProduct()">
                                    <i class="fas fa-plus"></i> Agregar Producto
                                </button>
                            </div>

                            <div id="products-container">
                                <!-- Products will be added here dynamically -->
                            </div>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('calculations.create') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-calculator"></i> Calcular Impuestos
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let productIndex = 0;
const senaeLiberations = @json($liberations ?? []);

function buildLiberationOptions() {
    return senaeLiberations.map(lib => {
        const legalBasis = (lib.legal_basis || lib.description || '').replace(/"/g, '&quot;');
        return `<option value="${lib.code}" data-exempts-iva="${lib.exempts_iva ? 1 : 0}" data-exempts-tariff="${lib.exempts_tariff ? 1 : 0}" data-legal-basis="${legalBasis}">${lib.code} - ${lib.description} (${lib.type})</option>`;
    }).join('');
}

function addProduct() {
    const container = document.getElementById('products-container');
    const liberationOptions = buildLiberationOptions();

    const productHtml = `
        <div class="card mb-3 product-item" data-index="${productIndex}">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Producto ${productIndex + 1}</h6>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeProduct(${productIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Part Number</label>
                            <input type="text" class="form-control" name="products[${productIndex}][part_number]">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Descripción en Inglés</label>
                            <input type="text" class="form-control" name="products[${productIndex}][description_en]" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Descripción en Español</label>
                            <input type="text" class="form-control" name="products[${productIndex}][description_es]">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Código Arancelario (HS)</label>
                            <input type="text" class="form-control" name="products[${productIndex}][hs_code]"
                                   placeholder="Ej: 0101210000">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Cantidad</label>
                            <input type="number" step="0.01" min="0.01" class="form-control"
                                   name="products[${productIndex}][quantity]" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Precio Unitario FOB (USD)</label>
                            <input type="number" step="0.01" min="0.01" class="form-control"
                                   name="products[${productIndex}][unit_price_fob]" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label class="form-label">Peso Unitario (Kg)</label>
                            <input type="number" step="0.01" min="0" class="form-control"
                                   name="products[${productIndex}][unit_weight]">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="mb-3">
                            <label class="form-label">Exención ICE</label>
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" 
                                       name="products[${productIndex}][ice_exempt]" value="1"
                                       onchange="toggleIceExemptReason(${productIndex})">
                                <label class="form-check-label">Exento de ICE</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8" id="ice-exempt-reason-${productIndex}" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Razón de Exención ICE</label>
                            <input type="text" class="form-control" 
                                   name="products[${productIndex}][ice_exempt_reason]"
                                   placeholder="Especifique la razón de la exención">
                        </div>
                    </div>
                </div>

                <div class="border-top pt-2 mt-2">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-2">
                                <label class="form-label small mb-1 font-weight-bold"><i class="fas fa-file-contract"></i> Código Liberatorio / TPNG (SENAE)</label>
                                <select class="form-select form-select-sm" id="liberation-select-${productIndex}" onchange="handleLiberationChange(${productIndex})">
                                    <option value="">-- Ninguno (Tarifa General) --</option>
                                    ${liberationOptions}
                                    <option value="__custom__">-- Otro código manual --</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6" id="custom-liberation-wrapper-${productIndex}" style="display: none;">
                            <div class="mb-2">
                                <label class="form-label small mb-1">Código TPNG Manual</label>
                                <input type="text" class="form-control form-control-sm" id="custom-liberation-input-${productIndex}" oninput="handleCustomLiberationInput(${productIndex})" placeholder="Ej: 0672">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="products[${productIndex}][liberation_code]" id="liberation-code-${productIndex}" value="">

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-2">
                                <div class="form-check form-check-inline mt-1">
                                    <input type="checkbox" class="form-check-input" 
                                           name="products[${productIndex}][iva_exempt]" id="iva-exempt-${productIndex}" value="1">
                                    <label class="form-check-label small" for="iva-exempt-${productIndex}">Exento IVA (0%)</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm" 
                                       name="products[${productIndex}][iva_exempt_reason]" id="iva-exempt-reason-${productIndex}"
                                       placeholder="Razón Exención IVA">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <div class="form-check form-check-inline mt-1">
                                    <input type="checkbox" class="form-check-input" 
                                           name="products[${productIndex}][tariff_exempt]" id="tariff-exempt-${productIndex}" value="1">
                                    <label class="form-check-label small" for="tariff-exempt-${productIndex}">Exento Arancel (0%)</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <input type="text" class="form-control form-control-sm" 
                                       name="products[${productIndex}][tariff_exempt_reason]" id="tariff-exempt-reason-${productIndex}"
                                       placeholder="Razón Exención Arancel">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', productHtml);
    productIndex++;
}

function removeProduct(index) {
    const productItem = document.querySelector(`[data-index="${index}"]`);
    if (productItem) {
        productItem.remove();
    }
    
    updateProductNumbers();
}

function updateProductNumbers() {
    const products = document.querySelectorAll('.product-item');
    products.forEach((product, index) => {
        const header = product.querySelector('.card-header h6');
        header.textContent = `Producto ${index + 1}`;
    });
}

function toggleIceExemptReason(index) {
    const checkbox = document.querySelector(`input[name="products[${index}][ice_exempt]"]`);
    const reasonDiv = document.getElementById(`ice-exempt-reason-${index}`);
    
    if (checkbox.checked) {
        reasonDiv.style.display = 'block';
    } else {
        reasonDiv.style.display = 'none';
    }
}

function handleLiberationChange(index) {
    const select = document.getElementById(`liberation-select-${index}`);
    const customDiv = document.getElementById(`custom-liberation-wrapper-${index}`);
    const customInput = document.getElementById(`custom-liberation-input-${index}`);
    const hidden = document.getElementById(`liberation-code-${index}`);
    const ivaCheck = document.getElementById(`iva-exempt-${index}`);
    const ivaReason = document.getElementById(`iva-exempt-reason-${index}`);
    const tariffCheck = document.getElementById(`tariff-exempt-${index}`);
    const tariffReason = document.getElementById(`tariff-exempt-reason-${index}`);

    const val = select.value;
    if (val === '__custom__') {
        customDiv.style.display = 'block';
        hidden.value = customInput.value.trim();
    } else if (val === '') {
        customDiv.style.display = 'none';
        hidden.value = '';
    } else {
        customDiv.style.display = 'none';
        hidden.value = val;
        const opt = select.options[select.selectedIndex];
        if (opt && opt.dataset) {
            if (opt.dataset.exemptsIva === '1') {
                ivaCheck.checked = true;
                if (!ivaReason.value.trim()) {
                    ivaReason.value = opt.dataset.legalBasis || ('Liberación SENAE ' + val);
                }
            }
            if (opt.dataset.exemptsTariff === '1') {
                tariffCheck.checked = true;
                if (!tariffReason.value.trim()) {
                    tariffReason.value = opt.dataset.legalBasis || ('Liberación SENAE ' + val);
                }
            }
        }
    }
}

function handleCustomLiberationInput(index) {
    const customInput = document.getElementById(`custom-liberation-input-${index}`);
    const hidden = document.getElementById(`liberation-code-${index}`);
    if (hidden && customInput) {
        hidden.value = customInput.value.trim();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    addProduct();
});
</script>
@endsection
