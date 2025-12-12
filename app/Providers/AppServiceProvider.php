<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
