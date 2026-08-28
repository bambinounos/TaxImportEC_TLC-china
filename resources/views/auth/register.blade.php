@extends('layouts.guest')

@section('title', 'Registrarse')

@section('content')
<div class="form-card">
    <div class="form-strip">
        <span class="t">Registrarse</span>
        <span class="n">Cuenta nueva</span>
    </div>

    <form id="register-form" method="POST" action="{{ route('register') }}">
        @csrf

        {{-- Anti-Bot Time-Gate Token --}}
        <input type="hidden" name="_form_rendered_at" value="{{ $timeToken ?? '' }}">

        {{-- Invisible Honeypot field (should remain empty for legitimate human users) --}}
        <div style="position: absolute; left: -9999px; top: -9999px; width: 1px; height: 1px; overflow: hidden; opacity: 0;" aria-hidden="true">
            <label for="website_hp">Sitio web (dejar en blanco)</label>
            <input type="text" id="website_hp" name="website_hp" value="" tabindex="-1" autocomplete="off">
        </div>

        {{-- Google reCAPTCHA v3 Response Token --}}
        <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

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

        <button type="submit" id="btn-submit" class="btn-submit">Registrarse</button>

        @if (config('services.recaptcha.site_key'))
            <p style="font-size: 0.68rem; color: #6E7A93; text-align: center; margin-top: 0.8rem; line-height: 1.3;">
                Este sitio está protegido por Google reCAPTCHA v3 bajo sus
                <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" style="color: #6E7A93;">Políticas de Privacidad</a> y
                <a href="https://policies.google.com/terms" target="_blank" rel="noopener" style="color: #6E7A93;">Términos de Servicio</a>.
            </p>
        @endif

        <p class="alt-link">¿Ya tienes cuenta? <a href="{{ route('login') }}">Inicia sesión aquí</a></p>
    </form>
</div>
@endsection

@push('scripts')
@if (config('services.recaptcha.site_key'))
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('register-form');
        const submitBtn = document.getElementById('btn-submit');
        const recaptchaSiteKey = '{{ config('services.recaptcha.site_key') }}';

        if (!form) return;

        form.addEventListener('submit', function (e) {
            if (typeof grecaptcha !== 'undefined' && recaptchaSiteKey) {
                e.preventDefault();

                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerText = 'Verificando...';
                }

                grecaptcha.ready(function () {
                    grecaptcha.execute(recaptchaSiteKey, { action: 'register' })
                        .then(function (token) {
                            document.getElementById('g-recaptcha-response').value = token;
                            form.submit();
                        })
                        .catch(function (error) {
                            console.error('Error al ejecutar Google reCAPTCHA:', error);
                            // Submit anyway to let backend handle fallback
                            form.submit();
                        });
                });
            }
        });
    });
</script>
@endif
@endpush
