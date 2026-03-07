@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h4>{{ $calculation->name }}</h4>
                        <small class="text-muted">
                            Creado el {{ $calculation->created_at->format('d/m/Y H:i') }} por {{ optional($calculation->user)->name ?? 'Usuario no encontrado' }}
                        </small>
                        @if(!$calculation->isOwnedBy(auth()->user()) && $calculation->isSharedWith(auth()->user()))
                            <br><small class="text-info"><i class="fas fa-share-alt"></i> Compartido contigo ({{ $calculation->getSharePermission(auth()->user()) === 'edit' ? 'Editar' : 'Solo ver' }})</small>
                        @endif
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('calculations.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                        <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Exportar
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('calculations.export-csv', $calculation) }}">
                                <i class="fas fa-file-csv"></i> Exportar CSV
                            </a></li>
                            <li><a class="dropdown-item" href="{{ route('calculations.export-excel', $calculation) }}">
                                <i class="fas fa-file-excel"></i> Exportar Excel
                            </a></li>
                        </ul>
                        @can('share', $calculation)
                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#shareModal">
                            <i class="fas fa-share-alt"></i> Compartir
                        </button>
                        @endcan
                    </div>
                </div>

                <div class="card-body">
                    @if($calculation->description)
                        <p class="text-muted">{{ $calculation->description }}</p>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">TLC China</h6>
                                    @if($calculation->use_tlc_china)
                                        <span class="badge bg-success fs-6">Activado</span>
                                    @else
                                        <span class="badge bg-secondary fs-6">Desactivado</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Año de Cálculo</h6>
                                    <h5 class="text-primary">{{ $calculation->calculation_year }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Método Prorrateo</h6>
                                    <span class="badge bg-info fs-6">
                                        {{ $calculation->proration_method == 'weight' ? 'Por Peso' : 'Por Precio' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body text-center">
                                    <h6 class="card-title">Total Items</h6>
                                    <h5 class="text-info">{{ $calculation->items->count() }}</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($calculation->items->count() == 0)
                        <div class="text-center py-5">
                            <h5>No hay productos importados</h5>
                            <p class="text-muted">Importe un archivo CSV para comenzar con los cálculos.</p>

                            @can('update', $calculation)
                            <form method="POST" action="{{ route('calculations.import-csv', $calculation) }}"
                                  enctype="multipart/form-data" class="d-inline-block">
                                @csrf
                                <div class="mb-3">
                                    <input type="file" class="form-control" name="csv_file" accept=".csv,.txt" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload"></i> Importar CSV
                                </button>
                            </form>
                            @endcan
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Productos Importados</h5>
                            @can('update', $calculation)
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="fas fa-sync-alt"></i> Sincronizar CSV
                                </button>
                                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#createItemModal">
                                    <i class="fas fa-plus"></i> Agregar Item
                                </button>
                                <form method="POST" action="{{ route('calculations.calculate', $calculation) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-calculator"></i> Recalcular
                                    </button>
                                </form>
                            </div>
                            @endcan
                        </div>

                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Producto</th>
                                        <th>Partida</th>
                                        <th>Cantidad</th>
                                        <th>Peso Unit.</th>
                                        <th>Precio FOB</th>
                                        <th>CIF</th>
                                        <th>Arancel</th>
                                        <th>FODINFA</th>
                                        <th>ICE</th>
                                        <th>IVA</th>
                                        <th>Total</th>
                                        <th>Ganancia</th>
                                        <th>Precio Venta</th>
                                        @can('update', $calculation)
                                        <th>Acciones</th>
                                        @endcan
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($calculation->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->description_en }}</strong>
                                            @if($item->description_es)
                                                <br><small class="text-muted">{{ $item->description_es }}</small>
                                            @endif
                                            @if($item->part_number)
                                                <br><small class="text-info">P/N: {{ $item->part_number }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <code>{{ $item->hs_code }}</code>
                                            @if($item->tariffCode)
                                                <br><small class="text-muted">{{ Str::limit($item->tariffCode->description_es, 30) }}</small>
                                            @endif
                                        </td>
                                        <td>{{ number_format($item->quantity) }}</td>
                                        <td>{{ number_format($item->unit_weight, 3) }} kg</td>
                                        <td>${{ number_format($item->unit_price_fob, 2) }}</td>
                                        <td>${{ number_format($item->cif_value, 2) }}</td>
                                        <td>${{ number_format($item->tariff_amount, 2) }}</td>
                                        <td>${{ number_format($item->fodinfa_amount, 2) }}</td>
                                        <td>
                                            ${{ number_format($item->ice_amount, 2) }}
                                            @if($item->ice_exempt)
                                                <br><small class="badge bg-warning text-dark" title="{{ $item->ice_exempt_reason }}">EXENTO</small>
                                            @endif
                                        </td>
                                        <td>${{ number_format($item->iva_amount, 2) }}</td>
                                        <td><strong>${{ number_format($item->total_cost, 2) }}</strong></td>
                                        <td>
                                            @if($item->profit_margin_percent !== null)
                                                <span class="badge bg-warning text-dark" title="Margen individual">{{ number_format($item->profit_margin_percent, 2) }}%</span>
                                            @else
                                                <span class="badge bg-secondary" title="Margen global">{{ number_format($calculation->profit_margin_percent, 2) }}%</span>
                                            @endif
                                        </td>
                                        <td><strong>${{ number_format($item->sale_price, 2) }}</strong></td>
                                        @can('update', $calculation)
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('calculation-items.edit', $item) }}" class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('calculation-items.destroy', $item) }}" method="POST" onsubmit="return confirm('¿Está seguro de que desea eliminar este item?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        @endcan
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="5">TOTALES</th>
                                        <th>${{ number_format($calculation->items->sum('cif_value'), 2) }}</th>
                                        <th>${{ number_format($calculation->items->sum('tariff_amount'), 2) }}</th>
                                        <th>${{ number_format($calculation->items->sum('fodinfa_amount'), 2) }}</th>
                                        <th>${{ number_format($calculation->items->sum('ice_amount'), 2) }}</th>
                                        <th>${{ number_format($calculation->items->sum('iva_amount'), 2) }}</th>
                                        <th><strong>${{ number_format($calculation->items->sum('total_cost'), 2) }}</strong></th>
                                        <th></th>
                                        <th><strong>${{ number_format($calculation->items->sum('sale_price'), 2) }}</strong></th>
                                        @can('update', $calculation)<th></th>@endcan
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Resumen de Costos</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Total FOB:</td>
                                                <td class="text-end">${{ number_format($calculation->total_fob_value, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Flete:</td>
                                                <td class="text-end">${{ number_format($calculation->freight_cost, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Seguro ({{ $calculation->insurance_rate }}%):</td>
                                                <td class="text-end">${{ number_format($calculation->total_fob_value * $calculation->insurance_rate / 100, 2) }}</td>
                                            </tr>
                                            <tr class="table-secondary">
                                                <td><strong>Total CIF:</strong></td>
                                                <td class="text-end"><strong>${{ number_format($calculation->items->sum('cif_value'), 2) }}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="mb-0">Resumen de Impuestos</h6>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-sm">
                                            <tr>
                                                <td>Aranceles:</td>
                                                <td class="text-end">${{ number_format($calculation->items->sum('tariff_amount'), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>FODINFA (0.5%):</td>
                                                <td class="text-end">${{ number_format($calculation->items->sum('fodinfa_amount'), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>ICE:</td>
                                                <td class="text-end">${{ number_format($calculation->items->sum('ice_amount'), 2) }}</td>
                                            </tr>
                                            <tr>
                                                <td>IVA:</td>
                                                <td class="text-end">${{ number_format($calculation->items->sum('iva_amount'), 2) }}</td>
                                            </tr>
                                            <tr class="table-secondary">
                                                <td><strong>Total Impuestos:</strong></td>
                                                <td class="text-end"><strong>${{ number_format($calculation->total_taxes, 2) }}</strong></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@can('update', $calculation)
<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sincronizar Productos desde CSV</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('calculations.import-csv', $calculation) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <strong>Atención:</strong> Este proceso reemplazará la lista de productos actual con el contenido del archivo CSV. Los productos existentes que no estén en el archivo serán eliminados.
                    </div>
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Archivo CSV</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                        <div class="form-text">
                            Columnas requeridas: <strong>part_number, description_en, quantity, unit_price_fob</strong>.
                            <br>
                            Columnas opcionales: description_es, unit_weight, hs_code, ice_exempt, ice_exempt_reason, profit_margin_percent.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-sync-alt"></i> Sincronizar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Create Item Modal -->
<div class="modal fade" id="createItemModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('calculations.items.store', $calculation) }}">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Part Number</label>
                                <input type="text" class="form-control" name="part_number" value="{{ old('part_number') }}" required>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label class="form-label">Descripción en Inglés</label>
                                <input type="text" class="form-control" name="description_en" value="{{ old('description_en') }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Descripción en Español</label>
                                <input type="text" class="form-control" name="description_es" value="{{ old('description_es') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Código Arancelario (HS)</label>
                                <input type="text" class="form-control" name="hs_code" value="{{ old('hs_code') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Cantidad</label>
                                <input type="number" step="1" min="1" class="form-control" name="quantity" value="{{ old('quantity') }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Precio Unitario FOB (USD)</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" name="unit_price_fob" value="{{ old('unit_price_fob') }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Peso Unitario (Kg)</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="unit_weight" value="{{ old('unit_weight') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label">Ganancia Individual (%)</label>
                                <input type="number" step="0.01" min="0" max="1000" class="form-control" name="profit_margin_percent" value="{{ old('profit_margin_percent') }}" placeholder="Usar global">
                                <div class="form-text">Dejar vacío para usar el margen global ({{ number_format($calculation->profit_margin_percent, 2) }}%)</div>
                            </div>
                        </div>
                    </div>
                     <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Exención ICE</label>
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="ice_exempt" value="1" {{ old('ice_exempt') ? 'checked' : '' }}>
                                    <label class="form-check-label">Exento de ICE</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Razón de Exención ICE</label>
                                <input type="text" class="form-control" name="ice_exempt_reason" value="{{ old('ice_exempt_reason') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Agregar Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

        <!-- Profit Margin Configuration Section -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Configuración de Margen de Ganancia</h5>
                @can('update', $calculation)
                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#profitMarginModal">
                    <i class="fas fa-percentage"></i> Editar Margen Global
                </button>
                @endcan
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><strong>Margen de Ganancia Global:</strong></span>
                            <span class="badge bg-success fs-6">{{ number_format($calculation->profit_margin_percent, 2) }}%</span>
                        </div>
                        @php
                            $itemsWithCustomMargin = $calculation->items->whereNotNull('profit_margin_percent');
                        @endphp
                        @if($itemsWithCustomMargin->count() > 0)
                        <hr>
                        <h6>Items con Margen Individual:</h6>
                        <ul class="list-group list-group-flush">
                            @foreach($itemsWithCustomMargin as $item)
                            <li class="list-group-item d-flex justify-content-between align-items-center py-1">
                                <small>{{ $item->part_number }} - {{ Str::limit($item->description_en, 40) }}</small>
                                <span class="badge bg-warning text-dark">{{ number_format($item->profit_margin_percent, 2) }}%</span>
                            </li>
                            @endforeach
                        </ul>
                        @endif
                        <hr>
                        <small class="text-muted">
                            El margen global se aplica a todos los productos que no tienen un margen individual configurado.
                            <br>
                            <strong>Fórmula:</strong> Precio de Venta = Costo Total x (1 + Margen/100)
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Local Expenses Configuration Section -->
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>Configuración de Gastos Locales</h5>
                @can('update', $calculation)
                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#localExpensesModal">
                    <i class="fas fa-edit"></i> Editar Gastos
                </button>
                @endcan
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Costos Pre-Impuestos</h6>
                        @if($calculation->additional_costs_pre_tax)
                            @foreach($calculation->additional_costs_pre_tax as $name => $amount)
                                <div class="d-flex justify-content-between">
                                    <span>{{ $name }}</span>
                                    <span>${{ number_format($amount, 2) }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">No hay costos pre-impuestos configurados</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6>Costos Post-Impuestos</h6>
                        @if($calculation->additional_costs_post_tax)
                            @foreach($calculation->additional_costs_post_tax as $name => $cost)
                                <div class="d-flex justify-content-between">
                                    <span>{{ $name }} @if(is_array($cost) && ($cost['iva_applies'] ?? false))<small class="text-info">(+IVA)</small>@endif</span>
                                    <span>${{ number_format(is_array($cost) ? $cost['amount'] : $cost, 2) }}</span>
                                </div>
                            @endforeach
                        @else
                            <p class="text-muted">No hay costos post-impuestos configurados</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sharing Section --}}
        @can('share', $calculation)
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-share-alt"></i> Compartir Cálculo</h5>
            </div>
            <div class="card-body">
                @if($calculation->shares->count() > 0)
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Permiso</th>
                            <th>Compartido el</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($calculation->shares as $share)
                        <tr>
                            <td>{{ $share->sharedWithUser->name }} <small class="text-muted">({{ $share->sharedWithUser->email }})</small></td>
                            <td>
                                @if($share->permission === 'edit')
                                    <span class="badge bg-success">Editar</span>
                                @else
                                    <span class="badge bg-info">Solo ver</span>
                                @endif
                            </td>
                            <td>{{ $share->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <form action="{{ route('calculations.revoke-share', [$calculation, $share->sharedWithUser]) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Revocar acceso para {{ $share->sharedWithUser->name }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times"></i> Revocar
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <p class="text-muted">Este cálculo no está compartido con nadie.</p>
                @endif
            </div>
        </div>
        @endcan

        {{-- Audit Log Section --}}
        @if($calculation->auditLogs->count() > 0)
        <div class="card mt-4">
            <div class="card-header">
                <h5><i class="fas fa-history"></i> Historial de Cambios</h5>
            </div>
            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($calculation->auditLogs->take(50) as $log)
                        <tr>
                            <td><small>{{ $log->created_at->format('d/m/Y H:i:s') }}</small></td>
                            <td><small>{{ $log->user->name ?? 'N/A' }}</small></td>
                            <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                            <td><small>{{ $log->description }}</small></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>

    @can('update', $calculation)
    <!-- Profit Margin Edit Modal -->
    <div class="modal fade" id="profitMarginModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('calculations.update-profit-margin', $calculation) }}" id="profitMarginForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Margen de Ganancia Global</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="profit_margin_percent" class="form-label">Margen de Ganancia Global (%)</label>
                            <input type="number" step="0.01" min="0" max="1000" class="form-control"
                                   id="profit_margin_percent" name="profit_margin_percent"
                                   value="{{ $calculation->profit_margin_percent }}" required>
                            <div class="form-text">
                                Este margen se aplica a todos los productos que <strong>no</strong> tienen un margen individual.
                                <br>
                                <strong>Ejemplo:</strong> Si ingresa 25%, el precio de venta será el costo total x 1.25
                            </div>
                        </div>
                        <div class="alert alert-info">
                            <strong>Nota:</strong> Al cambiar el margen global, se recalcularán los precios de venta de todos los productos (excepto los que tienen margen individual).
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Actualizar y Recalcular</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Local Expenses Edit Modal -->
    <div class="modal fade" id="localExpensesModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="{{ route('calculations.update-local-expenses', $calculation) }}" id="localExpensesForm">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Gastos Locales</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        @include('calculations.partials.local-expenses-modal', [
                            'defaultPreTaxCosts' => $calculation->additional_costs_pre_tax ?? [],
                            'defaultPostTaxCosts' => $calculation->additional_costs_post_tax ?? []
                        ])
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar y Recalcular</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Share Modal --}}
    @can('share', $calculation)
    <div class="modal fade" id="shareModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('calculations.share', $calculation) }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-share-alt"></i> Compartir Cálculo</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="shared_with_user_id" class="form-label">Seleccionar Usuario</label>
                            <select class="form-select" name="shared_with_user_id" id="shared_with_user_id" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($availableUsers as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="permission" class="form-label">Tipo de Permiso</label>
                            <select class="form-select" name="permission" id="permission" required>
                                <option value="view">Solo ver</option>
                                <option value="edit">Ver y editar</option>
                            </select>
                            <div class="form-text">
                                <strong>Solo ver:</strong> El usuario puede ver el cálculo y exportar, pero no puede modificarlo.
                                <br>
                                <strong>Ver y editar:</strong> El usuario puede modificar items, gastos y márgenes de ganancia.
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info">
                            <i class="fas fa-share-alt"></i> Compartir
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan
@endsection
