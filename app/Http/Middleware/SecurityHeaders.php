<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request and apply strict HTTP security headers.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Remover cabecera de divulgación de tecnología de servidor
        $response->headers->remove('X-Powered-By');
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
        }

        // Configuración de cabeceras de endurecimiento HTTP
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Directiva básica y flexible de Content-Security-Policy compatible con Vite, Tailwind y Google Fonts
        $csp = "default-src 'self' https: data:; "
            . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https:; "
            . "style-src 'self' 'unsafe-inline' https: fonts.googleapis.com; "
            . "font-src 'self' https: data: fonts.gstatic.com; "
            . "img-src 'self' data: https: blob:; "
            . "connect-src 'self' https:;";

        if (!$response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', $csp);
        }

        return $response;
    }
}
