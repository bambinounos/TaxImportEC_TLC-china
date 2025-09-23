<div class="card mt-4">
    <div class="card-header">
        <h5>Gastos Locales</h5>
    </div>
    <div class="card-body">
        <!-- Pre-Tax Costs Section -->
        <div class="mb-4">
            <h6>Otros Costos Pre-Impuestos (0% IVA)</h6>
            <div id="pre-tax-costs">
                <!-- Dynamic rows will be added here -->
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addPreTaxCost()">
                <i class="fas fa-plus"></i> Agregar Costo Pre-Impuesto
            </button>
        </div>

        <!-- Post-Tax Costs Section -->
        <div class="mb-4">
            <h6>Otros Costos Post-Impuestos</h6>
            <div id="post-tax-costs">
                <!-- Dynamic rows will be added here -->
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addPostTaxCost()">
                <i class="fas fa-plus"></i> Agregar Costo Post-Impuesto
            </button>
        </div>
    </div>
</div>

<script>
let preTaxIndex = 0;
let postTaxIndex = 0;

function addPreTaxCost(name = '', amount = 0) {
    const html = `
        <div class="row mb-2 pre-tax-cost-row" data-index="${preTaxIndex}">
            <div class="col-md-6">
                <input type="text" class="form-control" name="additional_costs_pre_tax[${preTaxIndex}][name]" 
                       placeholder="Nombre del costo" value="${name}" required>
            </div>
            <div class="col-md-4">
                <input type="number" step="0.01" min="0" class="form-control" 
                       name="additional_costs_pre_tax[${preTaxIndex}][amount]" 
                       placeholder="Monto USD" value="${amount}" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePreTaxCost(${preTaxIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('pre-tax-costs').insertAdjacentHTML('beforeend', html);
    preTaxIndex++;
}

function addPostTaxCost(name = '', amount = 0, ivaApplies = false) {
    const html = `
        <div class="row mb-2 post-tax-cost-row" data-index="${postTaxIndex}">
            <div class="col-md-5">
                <input type="text" class="form-control" name="additional_costs_post_tax[${postTaxIndex}][name]" 
                       placeholder="Nombre del costo" value="${name}" required>
            </div>
            <div class="col-md-3">
                <input type="number" step="0.01" min="0" class="form-control" 
                       name="additional_costs_post_tax[${postTaxIndex}][amount]" 
                       placeholder="Monto USD" value="${amount}" required>
            </div>
            <div class="col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" 
                           name="additional_costs_post_tax[${postTaxIndex}][iva_applies]" 
                           value="1" ${ivaApplies ? 'checked' : ''}>
                    <label class="form-check-label">IVA 15%</label>
                </div>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePostTaxCost(${postTaxIndex})">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    `;
    document.getElementById('post-tax-costs').insertAdjacentHTML('beforeend', html);
    postTaxIndex++;
}

function removePreTaxCost(index) {
    document.querySelector(`.pre-tax-cost-row[data-index="${index}"]`).remove();
}

function removePostTaxCost(index) {
    document.querySelector(`.post-tax-cost-row[data-index="${index}"]`).remove();
}

document.addEventListener('DOMContentLoaded', function() {
    @if(isset($defaultPreTaxCosts) && $defaultPreTaxCosts)
        @foreach($defaultPreTaxCosts as $name => $amount)
            addPreTaxCost('{{ $name }}', {{ $amount }});
        @endforeach
    @endif
    
    @if(isset($defaultPostTaxCosts) && $defaultPostTaxCosts)
        @foreach($defaultPostTaxCosts as $name => $cost)
            @if(is_array($cost))
                addPostTaxCost('{{ $name }}', {{ $cost['amount'] ?? 0 }}, {{ $cost['iva_applies'] ?? false ? 'true' : 'false' }});
            @else
                addPostTaxCost('{{ $name }}', {{ $cost }}, false);
            @endif
        @endforeach
    @endif
});
</script>
</div>
