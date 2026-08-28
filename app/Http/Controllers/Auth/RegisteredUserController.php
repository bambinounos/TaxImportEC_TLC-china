<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Services\AntiBotService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(
        protected AntiBotService $antiBotService
    ) {}

    public function create(): View
    {
        $timeToken = $this->antiBotService->generateTimeToken();

        return view('auth.register', compact('timeToken'));
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. Honeypot check: reject if hidden field contains any value
        if (!$this->antiBotService->validateHoneypot($request->input('website_hp'))) {
            Log::warning('Bot registration blocked (Honeypot triggered)', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'honeypot_value' => $request->input('website_hp'),
            ]);

            throw ValidationException::withMessages([
                'name' => 'No fue posible procesar la solicitud. Por favor intenta nuevamente.',
            ]);
        }

        // 2. Time-Gate check: reject submissions faster than humanly possible (< 3s) or expired (> 2h)
        if (!$this->antiBotService->validateTimeToken($request->input('_form_rendered_at'))) {
            Log::warning('Bot registration blocked (Time-Gate triggered)', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
            ]);

            throw ValidationException::withMessages([
                'name' => 'El formulario se completó demasiado rápido o la sesión expiró. Por favor completa los datos e intenta nuevamente.',
            ]);
        }

        // 3. Disposable/temporary email domain check
        if ($request->filled('email') && !$this->antiBotService->validateEmailDomain($request->input('email'))) {
            Log::warning('Bot registration blocked (Disposable/Invalid email domain)', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
            ]);

            throw ValidationException::withMessages([
                'email' => 'El dominio de correo electrónico ingresado no está permitido. Por favor usa un correo válido.',
            ]);
        }

        // 4. Google reCAPTCHA v3 score verification
        $recaptchaResult = $this->antiBotService->validateRecaptcha(
            $request->input('g-recaptcha-response'),
            $request->ip(),
            'register'
        );

        if (!$recaptchaResult['valid']) {
            Log::warning('Bot registration blocked (Google reCAPTCHA v3 failed)', [
                'ip' => $request->ip(),
                'email' => $request->input('email'),
                'reason' => $recaptchaResult['reason'],
                'score' => $recaptchaResult['score'],
            ]);

            throw ValidationException::withMessages([
                'email' => 'Verificación de seguridad no superada (reCAPTCHA). Por favor recarga la página e intenta nuevamente.',
            ]);
        }

        // 5. Standard form validation
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // 6. Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'is_active' => true,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
