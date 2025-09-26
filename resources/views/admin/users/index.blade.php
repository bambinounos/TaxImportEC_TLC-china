@extends('layouts.app')

@section('content')
<div class="container">
    <h1><i class="fas fa-users-cog"></i> Gestión de Usuarios</h1>
    <p class="text-muted">Administrar los usuarios del sistema</p>

    {{-- Search and Filter Form --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-search"></i> Filtrar y Buscar</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="form-inline">
                <div class="form-group mr-2 mb-2">
                    <label for="search" class="sr-only">Buscar</label>
                    <input type="text" class="form-control" id="search" name="search" placeholder="Nombre o email" value="{{ request('search') }}">
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="role" class="sr-only">Rol</label>
                    <select class="form-control" id="role" name="role">
                        <option value="">Todos los roles</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                        <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Usuario</option>
                    </select>
                </div>
                <div class="form-group mr-2 mb-2">
                    <label for="status" class="sr-only">Estado</label>
                    <select class="form-control" id="status" name="status">
                        <option value="">Todos los estados</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactivo</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary mb-2"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mb-2 ml-2"><i class="fas fa-sync-alt"></i> Limpiar</a>
            </form>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-user-friends"></i> Lista de Usuarios</h5>
            <span class="badge badge-pill badge-primary">{{ $users->total() }} usuarios</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Registrado el</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if ($user->isAdmin())
                                    <span class="badge badge-success">Admin</span>
                                @else
                                    <span class="badge badge-secondary">Usuario</span>
                                @endif
                            </td>
                            <td>
                                @if ($user->is_active)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-danger">Inactivo</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-info" title="Ver detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if (auth()->id() !== $user->id)
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Estás seguro de que quieres eliminar a este usuario? Esta acción no se puede deshacer.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" title="Eliminar usuario">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No se encontraron usuarios.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="card-footer">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection