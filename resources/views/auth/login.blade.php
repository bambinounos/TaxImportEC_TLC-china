@extends('layouts.guest')

@section('title', 'Iniciar sesión')

@section('content')
<div class="form-card">
    <div class="form-strip">
        <span class="t">Iniciar sesión</span>
        <span class="n">Acceso</span>
    </div>

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label for="email">Correo electrónico</label>
            <input type="email" class="@error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="password">Contraseña</label>
            <input type="password" class="@error('password') is-invalid @enderror"
                   id="password" name="password" required autocomplete="current-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="check-row">
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Recordarme</label>
        </div>

        <button type="submit" class="btn-submit">Iniciar sesión</button>

        <p class="alt-link">¿No tienes cuenta? <a href="{{ route('register') }}">Regístrate aquí</a></p>
    </form>
</div>
@endsection
