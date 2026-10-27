<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $input = $request->input('login', $request->input('nombre', $request->input('email')));
        $password = $request->input('password');

        if (empty($input) || empty($password)) {
            return back()->withErrors([
                'login' => 'Por favor ingrese su usuario o correo electrónico y contraseña.',
            ])->withInput();
        }

        $throttleKey = Str::transliterate(Str::lower($input).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'login' => "Demasiados intentos de acceso. Por favor, vuelva a intentarlo en {$seconds} segundos.",
            ])->withInput();
        }

        $field = filter_var($input, FILTER_VALIDATE_EMAIL) ? 'email' : 'nombre';
        $credentials = [
            $field => $input,
            'password' => $password,
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

        // Si intentó con nombre pero falló, intentar con email por si acaso
        if ($field === 'nombre') {
            if (Auth::attempt(['email' => $input, 'password' => $password], $request->boolean('remember'))) {
                RateLimiter::clear($throttleKey);
                $request->session()->regenerate();
                $target = Auth::user()->hasActivePermissions() ? route('dashboard.index') : route('bienvenida');
                $intended = session()->get('url.intended');
                if ($intended === url('/') || empty($intended)) {
                    return redirect($target);
                }
                return redirect()->intended($target);
            }
        }

        RateLimiter::hit($throttleKey, 60);

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
