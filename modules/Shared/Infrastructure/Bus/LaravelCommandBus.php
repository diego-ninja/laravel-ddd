<?php

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Bus\Dispatcher;
use Modules\Shared\Application\Contracts\CommandBusInterface;
use Modules\Shared\Application\Contracts\CommandInterface;

final readonly class LaravelCommandBus implements CommandBusInterface
{
    public function __construct(
        private Dispatcher $dispatcher
    ) {}

    public function dispatch(CommandInterface $command): void
    {
        $this->dispatcher->dispatch($command);
    }

    public function register(string $commandClass, string $handlerClass): void
    {
        // TODO: Implement register() method.
    }

    public function addMiddleware(string $middlewareClass): void
    {
        // TODO: Implement addMiddleware() method.
    }
}
