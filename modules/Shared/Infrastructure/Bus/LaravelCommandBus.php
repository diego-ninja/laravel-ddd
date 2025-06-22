<?php

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Bus\Dispatcher;
use Modules\Shared\Application\Contracts\CommandBusInterface;
use Modules\Shared\Application\Contracts\CommandInterface;

class LaravelCommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher
    ) {}

    public function dispatch(CommandInterface $command): mixed
    {
        return $this->dispatcher->dispatch($command);
    }
}