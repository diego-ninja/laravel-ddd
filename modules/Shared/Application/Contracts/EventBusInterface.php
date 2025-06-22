<?php

namespace Modules\Shared\Application\Contracts;

use Modules\Shared\Domain\Contracts\DomainEventInterface;

interface EventBusInterface
{
    /**
     * Dispatch a domain event.
     */
    public function dispatch(DomainEventInterface $event): void;

    /**
     * Dispatch multiple domain events.
     */
    public function dispatchMultiple(array $events): void;

    /**
     * Register an event listener.
     */
    public function listen(string $eventClass, string $listenerClass): void;

    /**
     * Add middleware to the event pipeline.
     */
    public function addMiddleware(string $middlewareClass): void;
}