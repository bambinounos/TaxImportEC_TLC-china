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
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5>Productos Importados</h5>
                            <div class="btn-group">
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal">
                                    <i class="fas fa-upload"></i> Importar Más
                                </button>
                                <form method="POST" action="{{ route('calculations.calculate', $calculation) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-calculator"></i> Recalcular
                                    </button>
                                </form>
                            </div>
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
                                        <th>ICE</th>
                                        <th>IVA</th>
                                        <th>Total</th>
                                        <th>Precio Venta</th>
                                        <th>Acciones</th>
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
                                        <td>
                                            ${{ number_format($item->ice_amount, 2) }}
                                            @if($item->ice_exempt)
                                                <br><small class="badge bg-warning text-dark" title="{{ $item->ice_exempt_reason }}">EXENTO</small>
                                            @endif
                                        </td>
                                        <td>${{ number_format($item->iva_amount, 2) }}</td>
                                        <td><strong>${{ number_format($item->total_cost, 2) }}</strong></td>
                                        <td><strong>${{ number_format($item->sale_price, 2) }}</strong></td>
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
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="5">TOTALES</th>
                                        <th>${{ number_format($calculation->items->sum('cif_value'), 2) }}</th>
                                        <th>${{ number_format($calculation->items->sum('tariff_amount'), 2) }}</th>
                                        <th>${{ number_format($calculation->items->sum('ice_amount'), 2) }}</th>
                                        <th>${{ number_format($calculation->items->sum('iva_amount'), 2) }}</th>
                                        <th><strong>${{ number_format($calculation->items->sum('total_cost'), 2) }}</strong></th>
                                        <th><strong>${{ number_format($calculation->items->sum('sale_price'), 2) }}</strong></th>
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

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Importar Productos Adicionales</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('calculations.import-csv', $calculation) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="csv_file" class="form-label">Archivo CSV</label>
                        <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,.txt" required>
                        <div class="form-text">
                            El archivo debe contener las columnas: description_en, quantity, unit_price_fob, hs_code (opcional), unit_weight (opcional), ice_exempt (opcional), ice_exempt_reason (opcional)
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
