<?php

use App\Http\Middleware\CheckAppAdmin;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckUserAdmin;
use App\Http\Middleware\EnsureHasOperationalRole;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            SecurityHeaders::class,
            EnsureUserIsActive::class,
        ]);

        $middleware->api(append: [
            EnsureUserIsActive::class,
        ]);

        $middleware->alias([
            'user_admin' => CheckUserAdmin::class,
            'app_admin' => CheckAppAdmin::class,
            'role' => CheckRole::class,
            'has_permissions' => EnsureHasOperationalRole::class,
            'active_user' => EnsureUserIsActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
