<?php

namespace Modules\Shared\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Shared\Application\Contracts\CommandBusInterface;
use Modules\Shared\Application\Contracts\QueryBusInterface;
use Modules\Shared\Application\Contracts\EventBusInterface;

class SharedServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerBuses();
        $this->registerHelpers();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->publishMigrations();
        $this->loadMigrations();
        $this->registerHandlers();
    }

    /**
     * Register CQRS buses.
     */
    private function registerBuses(): void
    {
        // Command Bus
        $this->app->singleton(CommandBusInterface::class, function ($app) {
            return new \Modules\Shared\Infrastructure\Bus\LaravelCommandBus(
                $app->make(\Illuminate\Bus\Dispatcher::class)
            );
        });
        
        // Query Bus
        $this->app->singleton(QueryBusInterface::class, function ($app) {
            return new \Modules\Shared\Infrastructure\Bus\LaravelQueryBus(
                $app->make(\Illuminate\Bus\Dispatcher::class)
            );
        });
        
        // Event Bus
        $this->app->singleton(EventBusInterface::class, function ($app) {
            return new \Modules\Shared\Infrastructure\Bus\LaravelEventBus(
                $app->make(\Illuminate\Events\Dispatcher::class)
            );
        });
    }

    /**
     * Register helper services.
     */
    private function registerHelpers(): void
    {
        // Register common utilities and helpers
        
        // UUID Generator
        $this->app->singleton('ddd.uuid.generator', function () {
            return new \Ramsey\Uuid\UuidFactory();
        });

        // Event Store (placeholder for now)
        $this->app->singleton('ddd.event.store', function () {
            // Will be implemented with actual event store in Phase 2
            return null;
        });
    }

    /**
     * Publish migrations.
     */
    private function publishMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../Database/Migrations' => database_path('migrations'),
            ], 'ddd-migrations');
        }
    }

    /**
     * Load migrations.
     */
    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
    }

    /**
     * Register command and query handlers automatically.
     */
    private function registerHandlers(): void
    {
        // Auto-register command handlers
        $commandHandlers = \Modules\Shared\Infrastructure\Support\HandlerDiscovery::discoverCommandHandlers();
        foreach ($commandHandlers as $handler) {
            $this->app->bind($handler, $handler);
        }

        // Auto-register query handlers
        $queryHandlers = \Modules\Shared\Infrastructure\Support\HandlerDiscovery::discoverQueryHandlers();
        foreach ($queryHandlers as $handler) {
            $this->app->bind($handler, $handler);
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            CommandBusInterface::class,
            QueryBusInterface::class,
            EventBusInterface::class,
            'ddd.uuid.generator',
            'ddd.event.store',
        ];
    }
}