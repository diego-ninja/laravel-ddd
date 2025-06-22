<?php

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Events\Dispatcher;
use Modules\Shared\Application\Contracts\EventBusInterface;
use Modules\Shared\Domain\Contracts\DomainEventInterface;

final readonly class LaravelEventBus implements EventBusInterface
{
    public function __construct(
        private Dispatcher $dispatcher
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

    public function dispatch(DomainEventInterface $event): void
    {
        // TODO: Implement dispatch() method.
    }

    public function dispatchMultiple(array $events): void
    {
        // TODO: Implement dispatchMultiple() method.
    }

    public function listen(string $eventClass, string $listenerClass): void
    {
        // TODO: Implement listen() method.
    }

    public function addMiddleware(string $middlewareClass): void
    {
        // TODO: Implement addMiddleware() method.
    }
}
