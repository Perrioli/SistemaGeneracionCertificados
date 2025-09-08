<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // No agregues nada aquí por ahora
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('is-root', function (User $user) {
            return $user->role->name === 'Root';
        });

        Gate::define('is-admin-or-root', function (User $user) {
            return in_array($user->role->name, ['Administrador', 'Root']);
        });

        Gate::define('is-persona', function (User $user) {
            return $user->role->name === 'Persona';
        });
    }
}
