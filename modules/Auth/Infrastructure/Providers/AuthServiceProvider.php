<?php

declare(strict_types=1);

namespace Modules\Auth\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Auth module bindings here
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutes();
    }

    /**
     * Load the Auth module routes.
     */
    private function loadRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(module_path('Infrastructure/routes/api.php'));
    }
}