<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureHasOperationalRole
{
    /**
     * Handle an incoming request.
     * Si el usuario autenticado solo posee el rol 'registered' (sin permisos activos de rev, tec o admin),
     * se le restringe el acceso al dashboard y módulos operativos, redirigiéndolo a la pantalla de bienvenida.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$user->hasActivePermissions()) {
            return redirect()->route('bienvenida');
        }

        return $next($request);
    }
}
