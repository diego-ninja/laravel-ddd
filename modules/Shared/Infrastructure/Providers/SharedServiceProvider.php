<?php

namespace Modules\Shared\Infrastructure\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;
use Modules\Shared\Application\Contracts\CommandBus as CommandBusContract;
use Modules\Shared\Application\Contracts\QueryBus as QueryBusContract;
use Modules\Shared\Application\Contracts\UnitOfWork as UnitOfWorkContract;
use Modules\Shared\Application\Middleware\Command\AuditMiddleware;
use Modules\Shared\Application\Middleware\Command\LoggingMiddleware as CommandLoggingMiddleware;
use Modules\Shared\Application\Middleware\Command\UnitOfWorkMiddleware;
use Modules\Shared\Application\Middleware\Command\ValidationMiddleware;
use Modules\Shared\Application\Middleware\Query\CachingMiddleware;
use Modules\Shared\Application\Middleware\Query\LoggingMiddleware as QueryLoggingMiddleware;
use Modules\Shared\Application\Middleware\Query\PerformanceMiddleware;
use Modules\Shared\Infrastructure\Bus\CommandBus;
use Modules\Shared\Infrastructure\Bus\QueryBus;
use Modules\Shared\Infrastructure\Listeners\EventStoreListener;
use Modules\Shared\Infrastructure\Support\HandlerDiscovery;
use Modules\Shared\Infrastructure\UnitOfWork\UnitOfWork;
use Ramsey\Uuid\UuidFactory;

class SharedServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerUnitOfWork();
        $this->registerBuses();
        $this->registerMiddlewares();
        $this->registerHelpers();
    }

    /**
     * Bootstrap services.
     * @throws BindingResolutionException
     */
    public function boot(): void
    {
        $this->publishMigrations();
        $this->loadMigrations();
        $this->registerHandlers();

        $this->configureQueryBus();
        $this->configureCommandBus();
        $this->registerEventListeners();
    }

    private function registerUnitOfWork(): void
    {
        $this->app->singleton(UnitOfWorkContract::class, UnitOfWork::class);
    }

    /**
     * Register CQRS buses.
     */
    private function registerBuses(): void
    {
        // Command Bus
        $this->app->singleton(CommandBusContract::class, function ($app) {
            return new CommandBus($app);
        });

        // Query Bus
        $this->app->singleton(QueryBusContract::class, function ($app) {
            return new QueryBus($app);
        });

    }

    private function registerMiddlewares(): void
    {
        $this->app->singleton(CommandLoggingMiddleware::class);
        $this->app->singleton(ValidationMiddleware::class);
        $this->app->singleton(AuditMiddleware::class);
        $this->app->singleton(UnitOfWorkMiddleware::class);

        $this->app->singleton(CachingMiddleware::class);
        $this->app->singleton(QueryLoggingMiddleware::class);
        $this->app->singleton(PerformanceMiddleware::class);

    }

    /**
     * Register helper services.
     */
    private function registerHelpers(): void
    {
        // Register common utilities and helpers

        // UUID Generator
        $this->app->singleton('ddd.uuid.generator', function () {
            return new UuidFactory();
        });

        // Event Store (placeholder for now)
        $this->app->singleton('ddd.event.store', function () {
            // Will be implemented with actual event store in Phase 2
            return null;
        });
    }

    /**
     * Publish Migrations.
     */
    private function publishMigrations(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../Database/Migrations' => database_path('Migrations'),
            ], 'ddd-Migrations');
        }
    }

    /**
     * Load Migrations.
     */
    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }

    /**
     * Register command and query handlers automatically.
     */
    private function registerHandlers(): void
    {
        // Auto-register command handlers
        $commandHandlers = HandlerDiscovery::discoverCommandHandlers();
        foreach ($commandHandlers as $handler) {
            $this->app->bind($handler, $handler);
        }

        // Auto-register query handlers
        $queryHandlers = HandlerDiscovery::discoverQueryHandlers();
        foreach ($queryHandlers as $handler) {
            $this->app->bind($handler, $handler);
        }

        // Register command handlers with command bus
        $commandBus = $this->app->make(CommandBusContract::class);
        foreach ($commandHandlers as $handlerClass) {
            $commandClass = $this->getCommandClassFromHandler($handlerClass);
            if ($commandClass) {
                $commandBus->register($commandClass, $handlerClass);
            }
        }

        // Register query handlers with query bus
        $queryBus = $this->app->make(QueryBusContract::class);
        foreach ($queryHandlers as $handlerClass) {
            $queryClass = $this->getQueryClassFromHandler($handlerClass);
            if ($queryClass) {
                $queryBus->register($queryClass, $handlerClass);
            }
        }
    }

    private function getCommandClassFromHandler(string $handlerClass): ?string
    {
        // Extract command class from handler class name
        // e.g., CreateUserCommandHandler -> CreateUserCommand
        if (str_ends_with($handlerClass, 'Handler')) {
            $commandClass = str_replace('Handler', '', $handlerClass);
            if (class_exists($commandClass)) {
                return $commandClass;
            }
        }
        return null;
    }

    private function getQueryClassFromHandler(string $handlerClass): ?string
    {
        // Extract query class from handler class name
        // e.g., GetUsersQueryHandler -> GetUsersQuery
        if (str_ends_with($handlerClass, 'Handler')) {
            $queryClass = str_replace('Handler', '', $handlerClass);
            if (class_exists($queryClass)) {
                return $queryClass;
            }
        }
        return null;
    }

    /**
     * @throws BindingResolutionException
     */
    private function configureCommandBus(): void
    {
        $commandBus = $this->app->make(CommandBusContract::class);

        // Order matters: UnitOfWorkMiddleware should be LAST (closest to handler)
        $commandBus->addMiddleware(CommandLoggingMiddleware::class);     // 1. Log first
        $commandBus->addMiddleware(ValidationMiddleware::class);  // 2. Validate
        $commandBus->addMiddleware(AuditMiddleware::class);       // 3. Audit
        $commandBus->addMiddleware(UnitOfWorkMiddleware::class);         // 4. Transaction + Events
    }

    /**
     * @throws BindingResolutionException
     */
    private function configureQueryBus(): void
    {
        $queryBus = $this->app->make(QueryBusContract::class);

        $queryBus->addMiddleware(QueryLoggingMiddleware::class);     // Log for debugging
        $queryBus->addMiddleware(PerformanceMiddleware::class); // Monitor performance
        $queryBus->addMiddleware(CachingMiddleware::class);     // Cache results
    }

    /**
     * Register event listeners for Laravel's native event system.
     */
    private function registerEventListeners(): void
    {
        $this->app->singleton(EventStoreListener::class);

        // Register the EventStoreListener as a wildcard listener but only for domain events
        $this->app['events']->listen('*', function($eventName, $data) {
            $event = $data[0] ?? null;
            if ($event instanceof \Modules\Shared\Domain\Contracts\DomainEvent) {
                $listener = $this->app->make(EventStoreListener::class);
                $listener->handle($event);
            }
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            CommandBusContract::class,
            QueryBusContract::class,
            'ddd.uuid.generator',
            'ddd.event.store',
        ];
    }
}
