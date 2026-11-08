<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     * Garantiza que usuarios con flag 'activo' = false no puedan operar en el sistema,
     * invalidando inmediatamente su sesión o token.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->activo) {
            // Revocar tokens API si existieran
            $user->tokens()->delete();

            // Desconectar sesión web si está activa en el guard web
            if (Auth::guard('web')->check()) {
                Auth::guard('web')->logout();
                if ($request->hasSession()) {
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                }
            }

            if ($request->expectsJson() || $request->is('api/*')) {
                abort(401, 'Su cuenta ha sido desactivada. Comuníquese con un administrador.');
            }

            return redirect()->route('login')
                ->withErrors(['login' => 'Su cuenta ha sido desactivada. Comuníquese con un administrador.']);
        }

        return $next($request);
    }
}
