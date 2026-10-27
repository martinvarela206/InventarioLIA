<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Política de complejidad de contraseñas: estricta en producción, permisiva en local/testing
        Password::defaults(function () {
            return app()->isProduction()
                ? Password::min(8)->letters()->mixedCase()->numbers()
                : Password::min(4);
        });
        \Illuminate\Support\Facades\Gate::define('write-data', function ($user) {
            return $user->hasRole('user_admin') || $user->hasRole('coordinador') || $user->hasRole('tecnico');
        });

        \Illuminate\Support\Facades\Gate::define('delete-data', function ($user) {
            return $user->hasRole('user_admin') || $user->hasRole('coordinador');
        });

        \Illuminate\Support\Facades\Gate::define('manage-movements', function ($user) {
            return $user->hasRole('user_admin') || $user->hasRole('coordinador');
        });
    }
}
