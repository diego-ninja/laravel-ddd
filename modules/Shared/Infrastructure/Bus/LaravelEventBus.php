<?php

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Events\Dispatcher;
use Modules\Shared\Application\Contracts\EventBusInterface;
use Modules\Shared\Domain\Contracts\DomainEventInterface;

class LaravelEventBus implements EventBusInterface
{
    public function __construct(
        private readonly Dispatcher $dispatcher
    ) {}

    public function publish(DomainEventInterface $event): void
    {
        $this->dispatcher->dispatch($event);
    }

    public function publishAll(array $events): void
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }
}