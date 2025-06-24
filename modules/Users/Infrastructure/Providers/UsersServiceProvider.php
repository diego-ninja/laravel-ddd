<?php

namespace Modules\Users\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class UsersServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerRepositories();
        $this->registerServices();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadRoutes();
        $this->registerEventListeners();
    }

    /**
     * Register repositories.
     */
    private function registerRepositories(): void
    {
        $this->app->bind(
            \Modules\Users\Domain\Repositories\UserRepository::class,
            \Modules\Users\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository::class
        );
    }

    /**
     * Register domain services.
     */
    private function registerServices(): void
    {
        // Register domain services here
    }

    /**
     * Load the Users module routes.
     */
    private function loadRoutes(): void
    {
        Route::middleware('api')
            ->prefix('api')
            ->group(module_path('Infrastructure/routes/api.php'));
    }

    /**
     * Register event listeners.
     */
    private function registerEventListeners(): void
    {
        // Register event listeners here
    }
}
