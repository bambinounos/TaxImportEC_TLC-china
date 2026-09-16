@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1><i class="fas fa-file-contract text-primary"></i> Códigos Liberatorios / TPNG (SENAE)</h1>
                    <p class="text-muted mb-0">Catálogo oficial de códigos aduaneros para exoneración o reducción de IVA, Aranceles e ICE en SENAE / Ecuapass</p>
                </div>
                <div>
                    <button type="button" class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#createLiberationModal">
                        <i class="fas fa-plus"></i> Nuevo Código
                    </button>
                    <a href="{{ route('admin.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al Panel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filtros de búsqueda -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.liberations.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Buscar por Código, Descripción o Base Legal</label>
                                <input type="text" class="form-control" id="search" name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Ej: 0672, maquinaria agrícola, LORTI...">
                            </div>
                            <div class="col-md-3">
                                <label for="type" class="form-label">Tipo de Liberación</label>
                                <select class="form-select" id="type" name="type">
                                    <option value="">Todos los tipos</option>
                                    <option value="TPNG" {{ request('type') === 'TPNG' ? 'selected' : '' }}>TPNG (Tratado Preferencial Norma Genérica)</option>
                                    <option value="DIP" {{ request('type') === 'DIP' ? 'selected' : '' }}>DIP (Cuerpo Diplomático / Organismos)</option>
                                    <option value="PUB" {{ request('type') === 'PUB' ? 'selected' : '' }}>PUB (Sector Público)</option>
                                    <option value="DON" {{ request('type') === 'DON' ? 'selected' : '' }}>DON (Donaciones / Asistencia)</option>
                                    <option value="ESP" {{ request('type') === 'ESP' ? 'selected' : '' }}>ESP (Régimen Especial / Otros)</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="status" class="form-label">Estado</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Todos</option>
                                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="{{ route('admin.liberations.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i> Limpiar
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de resultados -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h5 class="mb-0 font-weight-bold">Listado de Códigos Liberatorios</h5>
                    <span class="badge bg-primary fs-6">{{ $liberations->total() }} registros</span>
                </div>
                <div class="card-body p-0">
                    @if($liberations->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 110px;">Código TPNG</th>
                                        <th style="width: 90px;">Tipo</th>
                                        <th>Descripción</th>
                                        <th>Base Legal</th>
                                        <th style="width: 220px;">Beneficios Aplicables</th>
                                        <th style="width: 100px;">Estado</th>
                                        <th style="width: 130px;" class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($liberations as $lib)
                                    <tr>
                                        <td>
                                            <span class="badge bg-dark font-monospace fs-6 px-2 py-1">{{ $lib->code }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $lib->type }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $lib->description }}</strong>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ $lib->legal_basis ?: 'N/A' }}</small>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @if($lib->exempts_iva)
                                                    <span class="badge bg-success" title="Exención de IVA">
                                                        <i class="fas fa-check"></i> IVA 0%
                                                    </span>
                                                @endif
                                                @if($lib->exempts_tariff)
                                                    <span class="badge bg-info text-dark" title="Exención Arancelaria">
                                                        <i class="fas fa-check"></i> Arancel 0%
                                                    </span>
                                                @endif
                                                @if($lib->exempts_ice)
                                                    <span class="badge bg-warning text-dark" title="Exención ICE">
                                                        <i class="fas fa-check"></i> ICE Exento
                                                    </span>
                                                @endif
                                                @if(!$lib->exempts_iva && !$lib->exempts_tariff && !$lib->exempts_ice)
                                                    <span class="badge bg-light text-muted border">Sin beneficios automáticos</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($lib->is_active)
                                                <span class="badge bg-success">Activo</span>
                                            @else
                                                <span class="badge bg-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <button type="button" class="btn btn-sm btn-outline-primary me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal{{ $lib->id }}"
                                                    title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('admin.liberations.destroy', $lib) }}"
                                                  method="POST"
                                                  class="d-inline"
                                                  onsubmit="return confirm('¿Está seguro de eliminar el código liberatorio {{ $lib->code }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>

                                    <!-- Modal de Edición para este item -->
                                    <div class="modal fade" id="editModal{{ $lib->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.liberations.update', $lib) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title"><i class="fas fa-edit"></i> Editar Código Liberatorio: <strong>{{ $lib->code }}</strong></h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label">Código TPNG / SENAE <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control font-monospace" name="code" value="{{ old('code', $lib->code) }}" required maxlength="20">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label">Tipo <span class="text-danger">*</span></label>
                                                                <input type="text" class="form-control" name="type" value="{{ old('type', $lib->type) }}" required maxlength="50" placeholder="Ej: TPNG, DIP, PUB">
                                                            </div>
                                                            <div class="col-md-4 mb-3">
                                                                <label class="form-label">Estado</label>
                                                                <div class="form-check form-switch mt-2">
                                                                    <input type="checkbox" class="form-check-input" name="is_active" value="1" id="edit_active_{{ $lib->id }}" {{ old('is_active', $lib->is_active) ? 'checked' : '' }}>
                                                                    <label class="form-check-label" for="edit_active_{{ $lib->id }}">Activo para uso en cálculos</label>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Descripción Oficial <span class="text-danger">*</span></label>
                                                            <input type="text" class="form-control" name="description" value="{{ old('description', $lib->description) }}" required maxlength="255">
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Base Legal / Normativa</label>
                                                            <textarea class="form-control" name="legal_basis" rows="2" placeholder="Ej: LORTI Art. 55 Num. 5">{{ old('legal_basis', $lib->legal_basis) }}</textarea>
                                                        </div>

                                                        <div class="card bg-light p-3 mt-3">
                                                            <h6 class="font-weight-bold text-primary mb-3">Impuestos Exonerados</h6>
                                                            <div class="row">
                                                                <div class="col-md-4 mb-2">
                                                                    <div class="form-check">
                                                                        <input type="checkbox" class="form-check-input" name="exempts_iva" value="1" id="edit_iva_{{ $lib->id }}" {{ old('exempts_iva', $lib->exempts_iva) ? 'checked' : '' }}>
                                                                        <label class="form-check-label font-weight-bold" for="edit_iva_{{ $lib->id }}">Exonera IVA (Tarifa 0%)</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 mb-2">
                                                                    <div class="form-check">
                                                                        <input type="checkbox" class="form-check-input" name="exempts_tariff" value="1" id="edit_tariff_{{ $lib->id }}" {{ old('exempts_tariff', $lib->exempts_tariff) ? 'checked' : '' }}>
                                                                        <label class="form-check-label font-weight-bold" for="edit_tariff_{{ $lib->id }}">Exonera Arancel Ad-Valorem</label>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4 mb-2">
                                                                    <div class="form-check">
                                                                        <input type="checkbox" class="form-check-input" name="exempts_ice" value="1" id="edit_ice_{{ $lib->id }}" {{ old('exempts_ice', $lib->exempts_ice) ? 'checked' : '' }}>
                                                                        <label class="form-check-label font-weight-bold" for="edit_ice_{{ $lib->id }}">Exonera ICE</label>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-3">
                            {{ $liberations->links() }}
                        </div>
                    @else
                        <div class="p-5 text-center text-muted">
                            <i class="fas fa-inbox fa-3x mb-3"></i>
                            <p class="mb-0">No se encontraron códigos liberatorios con los criterios seleccionados.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear Nuevo Código Liberatorio -->
<div class="modal fade" id="createLiberationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('admin.liberations.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle text-success"></i> Registrar Código Liberatorio SENAE</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Código TPNG / SENAE <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace" name="code" value="{{ old('code') }}" required maxlength="20" placeholder="Ej: 0672">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Tipo <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="type" value="{{ old('type', 'TPNG') }}" required maxlength="50" placeholder="TPNG, DIP, PUB, etc.">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Estado</label>
                            <div class="form-check form-switch mt-2">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" id="create_active" checked>
                                <label class="form-check-label" for="create_active">Activo para cálculos</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción Oficial <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="description" value="{{ old('description') }}" required maxlength="255" placeholder="Ej: Maquinaria, implementos e insumos agropecuarios">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Base Legal / Normativa</label>
                        <textarea class="form-control" name="legal_basis" rows="2" placeholder="Ej: Ley de Régimen Tributario Interno Art. 55 Num. 5">{{ old('legal_basis') }}</textarea>
                    </div>

                    <div class="card bg-light p-3 mt-3">
                        <h6 class="font-weight-bold text-primary mb-3">Impuestos Exonerados</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="exempts_iva" value="1" id="create_iva" checked>
                                    <label class="form-check-label font-weight-bold" for="create_iva">Exonera IVA (Tarifa 0%)</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="exempts_tariff" value="1" id="create_tariff">
                                    <label class="form-check-label font-weight-bold" for="create_tariff">Exonera Arancel Ad-Valorem</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="exempts_ice" value="1" id="create_ice">
                                    <label class="form-check-label font-weight-bold" for="create_ice">Exonera ICE</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar Código</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
