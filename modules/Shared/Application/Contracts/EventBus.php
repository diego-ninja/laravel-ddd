<?php

namespace Modules\Shared\Application\Contracts;

use Modules\Shared\Domain\Contracts\DomainEvent;

interface EventBus extends Bus
{
    /**
     * Publish a single domain event through the middleware pipeline.
     *
     * @param DomainEvent $event The event to publish
     */
    public function publish(DomainEvent $event): void;

    /**
     * Publish multiple domain events.
     *
     * @param array<DomainEvent> $events Array of events to publish
     */
    public function publishAll(array $events): void;

    /**
     * Dispatch a domain event (alias for publish).
     *
     * @param DomainEvent $event The event to dispatch
     */
    public function dispatch(DomainEvent $event): void;

    /**
     * Dispatch multiple domain events (alias for publishAll).
     *
     * @param array<DomainEvent> $events Array of events to dispatch
     */
    public function dispatchMultiple(array $events): void;

    /**
     * Register an event listener.
     *
     * @param string $eventClass The event class name
     * @param string $listenerClass The listener class name
     */
    public function listen(string $eventClass, string $listenerClass): void;
}
