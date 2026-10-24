<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAppAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->isAppAdmin()) {
            abort(403, 'Acceso restringido a administradores de la aplicación (app_admin).');
        }

        return $next($request);
    }
}
