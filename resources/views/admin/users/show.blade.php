@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-user-tag"></i> Detalles del Usuario</h1>
        <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left"></i> Volver a la lista
        </a>
    </div>

    <div class="row">
        {{-- User Details Card --}}
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Información Principal</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless">
                        <tbody>
                            <tr>
                                <th style="width: 30%;">Nombre:</th>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <th>Email:</th>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <th>Rol:</th>
                                <td>
                                    @if ($user->isAdmin())
                                        <span class="badge badge-success">Administrador</span>
                                    @else
                                        <span class="badge badge-secondary">Usuario</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Estado:</th>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Registrado el:</th>
                                <td>{{ $user->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Última actualización:</th>
                                <td>{{ $user->updated_at->diffForHumans() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="card-footer text-right">
                    @if (auth()->id() !== $user->id)
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este usuario? Esta acción no se puede deshacer.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">
                                <i class="fas fa-trash-alt"></i> Eliminar Usuario
                            </button>
                        </form>
                    @else
                        <button class="btn btn-danger" disabled>
                            <i class="fas fa-trash-alt"></i> No puedes eliminarte a ti mismo
                        </button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Recent Activity Card --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Actividad Reciente</h5>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-calculator"></i> Últimos 10 Cálculos</h6>
                    @if ($user->calculations->isEmpty())
                        <p class="text-muted">Este usuario aún no ha realizado ningún cálculo.</p>
                    @else
                        <ul class="list-group list-group-flush">
                            @foreach ($user->calculations as $calculation)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <a href="{{ route('calculations.show', $calculation) }}">
                                        {{ $calculation->name ?: 'Cálculo sin nombre' }}
                                    </a>
                                    <small class="text-muted">{{ $calculation->created_at->diffForHumans() }}</small>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection