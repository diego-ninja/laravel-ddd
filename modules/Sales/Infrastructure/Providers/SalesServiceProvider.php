<?php

namespace Modules\Sales\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

class SalesServiceProvider extends ServiceProvider
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
        $this->registerEventListeners();
    }

    /**
     * Register repositories.
     */
    private function registerRepositories(): void
    {        $this->app->bind(
            \Modules\Sales\Domain\Repositories\OrderRepositoryInterface::class,
            \Modules\Sales\Infrastructure\Persistence\EloquentOrderRepository::class
        );        $this->app->bind(
            \Modules\Sales\Domain\Repositories\CustomerRepositoryInterface::class,
            \Modules\Sales\Infrastructure\Persistence\EloquentCustomerRepository::class
        );




        // Example:
        // $this->app->bind(
        //     \Modules\Sales\Domain\Repositories\ExampleRepositoryInterface::class,
        //     \Modules\Sales\Infrastructure\Persistence\EloquentExampleRepository::class
        // );
    }

    /**
     * Register domain services.
     */
    private function registerServices(): void
    {
        // Register domain services here
    }

    /**
     * Register event listeners.
     */
    private function registerEventListeners(): void
    {
        // Register event listeners here
    }
}