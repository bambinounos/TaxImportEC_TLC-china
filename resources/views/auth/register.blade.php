@extends('layouts.guest')

@section('title', 'Registrarse')

@section('content')
<div class="form-card">
    <div class="form-strip">
        <span class="t">Registrarse</span>
        <span class="n">Cuenta nueva</span>
    </div>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field">
            <label for="name">Nombre completo</label>
            <input type="text" class="@error('name') is-invalid @enderror"
                   id="name" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="email">Correo electrónico</label>
            <input type="email" class="@error('email') is-invalid @enderror"
                   id="email" name="email" value="{{ old('email') }}" required autocomplete="username">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="password">Contraseña</label>
            <input type="password" class="@error('password') is-invalid @enderror"
                   id="password" name="password" required autocomplete="new-password">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   required autocomplete="new-password">
        </div>

        <button type="submit" class="btn-submit">Registrarse</button>

        <p class="alt-link">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a></p>
    </form>
</div>
@endsection
