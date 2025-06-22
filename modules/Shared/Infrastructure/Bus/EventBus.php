<?php

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\Shared\Application\Contracts\EventBus as EventBusContract;
use Modules\Shared\Domain\Contracts\DomainEvent;

final  class EventBus extends AbstractBus implements EventBusContract
{
    /**
     * @throws BindingResolutionException
     */
    public function publish(DomainEvent $event): void
    {
        $this->executeWithMiddleware(
            $event,
            function (DomainEvent $event) {
                // Events can have multiple handlers
                $this->dispatchToAllHandlers($event);
            }
        );
    }

    /**
     * @throws BindingResolutionException
     */
    public function publishAll(array $events): void
    {
        foreach ($events as $event) {
            $this->publish($event);
        }
    }

    /**
     * @throws BindingResolutionException
     */
    public function dispatch(DomainEvent $event): void
    {
        $this->publish($event);
    }

    /**
     * @throws BindingResolutionException
     */
    public function dispatchMultiple(array $events): void
    {
        $this->publishAll($events);
    }

    public function listen(string $eventClass, string $listenerClass): void
    {
        $this->register($eventClass, $listenerClass);
    }

    /**
     * Dispatch event to all registered handlers.
     * @throws BindingResolutionException
     */
    private function dispatchToAllHandlers(DomainEvent $event): void
    {
        $eventClass = $event::class;

        // Events can have multiple handlers, so we need to handle this differently
        if (isset($this->handlers[$eventClass])) {
            $handler = $this->container->make($this->handlers[$eventClass]);
            $handler->handle($event);
        }

        // Also dispatch through Laravel's native event system for additional listeners
        $this->container->make('events')->dispatch($event);
    }
}
