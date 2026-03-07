@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-user-edit"></i> Editar Usuario</h1>
        <a href="{{ route('admin.users.show', $user) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Editar: {{ $user->name }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.users.update', $user) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" class="form-control" value="{{ $user->name }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label for="role" class="form-label">Rol</label>
                            <select class="form-select" name="role" id="role" {{ auth()->id() === $user->id ? 'disabled' : '' }}>
                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrador</option>
                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Usuario</option>
                                <option value="tariff_viewer" {{ $user->role === 'tariff_viewer' ? 'selected' : '' }}>Visor de Partidas</option>
                            </select>
                            @if(auth()->id() === $user->id)
                                <div class="form-text text-warning">No puedes cambiar tu propio rol.</div>
                            @else
                                <div class="form-text">
                                    <strong>Administrador:</strong> Acceso completo al sistema.<br>
                                    <strong>Usuario:</strong> Puede crear y gestionar sus propios cálculos.<br>
                                    <strong>Visor de Partidas:</strong> Solo puede consultar partidas arancelarias (no puede crear cálculos).
                                </div>
                            @endif
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" class="form-check-input" name="is_active" id="is_active" value="1" {{ $user->is_active ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Usuario Activo</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
