<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request checking for one or more permitted roles.
     * Usage: middleware('role:rev,tec,item_admin,user_admin')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Si se pasa una lista separada por comas en un solo argumento
        if (count($roles) === 1 && str_contains($roles[0], ',')) {
            $roles = explode(',', $roles[0]);
        }

        // Si es user_admin, tiene acceso maestro
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Comprobar si el usuario posee alguno de los roles requeridos
        if ($user->hasRole($roles)) {
            return $next($request);
        }

        abort(403, 'No tienes los permisos asignados para acceder a esta sección.');
    }
}
