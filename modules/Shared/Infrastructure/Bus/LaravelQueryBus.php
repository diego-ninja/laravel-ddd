<?php

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Bus\Dispatcher;
use Modules\Shared\Application\Contracts\QueryBusInterface;
use Modules\Shared\Application\Contracts\QueryInterface;

fianl readonly class LaravelQueryBus implements QueryBusInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher
    ) {}

    public function ask(QueryInterface $query): mixed
    {
        return $this->dispatcher->dispatch($query);
    }
}
