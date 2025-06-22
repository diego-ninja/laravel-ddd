<?php

namespace Modules\Sales\Application\Listeners;

use Modules\Sales\Domain\Events\OrderWasPlaced;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class OrderWasPlacedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(OrderWasPlaced $event): void
    {
        // Handle the domain event here
        
        // Examples:
        // - Send notifications
        // - Update read models
        // - Trigger other domain actions
        // - Log important events
        
        // Access event data:
        // $aggregateId = $event->aggregateId;
        // $occurredOn = $event->occurredOn;
        // $eventData = $event->toPrimitives();
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderWasPlaced $event, \Throwable $exception): void
    {
        // Handle the failed job
        // Log the error, send alerts, etc.
    }
}