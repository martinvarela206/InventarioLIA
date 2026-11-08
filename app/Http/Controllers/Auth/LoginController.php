<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $email = $request->input('email', $request->input('login'));
        $password = $request->input('password');

        if (empty($email) || empty($password)) {
            return back()->withErrors([
                'login' => 'Por favor ingrese su correo electrónico institucional y contraseña.',
            ])->withInput();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return back()->withErrors([
                'login' => 'El formato del correo electrónico no es válido.',
            ])->withInput();
        }

        $throttleKey = Str::transliterate(Str::lower($email).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login' => "Demasiados intentos de acceso. Por favor, vuelva a intentarlo en {$seconds} segundos.",
            ])->withInput();
        }

        $credentials = [
            'email' => $email,
            'password' => $password,
            'activo' => true,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();
            $target = Auth::user()->hasActivePermissions() ? route('dashboard.index') : route('bienvenida');
            $intended = session()->get('url.intended');
            if ($intended === url('/') || empty($intended)) {
                return redirect($target);
            }
            return redirect()->intended($target);
        }

        // Registrar intento fallido en el rate limiter preventivo contra fuerza bruta
        RateLimiter::hit($throttleKey, 60);

        // Mensaje unificado anti-enumeración de usuarios
        return back()->withErrors([
            'login' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
