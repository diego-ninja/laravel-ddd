<?php

namespace Modules\Sales\Application\Projectors;

use Modules\Sales\Application\DTOs\OrderHistoryItem;
use Modules\Sales\Domain\Events\DomainEvent;
// Import specific domain events
// use Modules\Sales\Domain\Events\EntityWasCreated;
// use Modules\Sales\Domain\Events\EntityWasUpdated;
// use Modules\Sales\Domain\Events\EntityWasDeleted;
use Illuminate\Support\Facades\DB;

class OrderHistoryItemProjector
{
    /**
     * Handle domain events to update read models.
     */
    public function handle(DomainEvent $event): void
    {
        match ($event::class) {
            // Map events to projection methods
            // EntityWasCreated::class => $this->projectEntityCreated($event),
            // EntityWasUpdated::class => $this->projectEntityUpdated($event),
            // EntityWasDeleted::class => $this->projectEntityDeleted($event),
            default => null, // Ignore unhandled events
        };
    }

    /**
     * Project entity creation event.
     */
    private function projectEntityCreated(/* EntityWasCreated */ $event): void
    {
        $readModelData = [
            'id' => $event->aggregateId,
            // Map event data to read model fields
            // 'name' => $event->name,
            // 'status' => $event->status,
            // 'amount' => $event->amount,
            'created_at' => $event->occurredOn->format('Y-m-d H:i:s'),
            'updated_at' => $event->occurredOn->format('Y-m-d H:i:s'),
        ];

        DB::table('orderHistoryItem_read_models')->insert($readModelData);
    }

    /**
     * Project entity update event.
     */
    private function projectEntityUpdated(/* EntityWasUpdated */ $event): void
    {
        $updateData = [
            // Map updated fields
            // 'name' => $event->name,
            // 'status' => $event->status,
            'updated_at' => $event->occurredOn->format('Y-m-d H:i:s'),
        ];

        DB::table('orderHistoryItem_read_models')
            ->where('id', $event->aggregateId)
            ->update($updateData);
    }

    /**
     * Project entity deletion event.
     */
    private function projectEntityDeleted(/* EntityWasDeleted */ $event): void
    {
        DB::table('orderHistoryItem_read_models')
            ->where('id', $event->aggregateId)
            ->delete();
    }

    /**
     * Rebuild entire read model from domain events.
     */
    public function rebuild(): void
    {
        // Clear existing read models
        DB::table('orderHistoryItem_read_models')->truncate();

        // Replay all relevant domain events
        $events = DB::table('domain_events')
            ->where('event_type', 'LIKE', '%Sales%')
            ->orderBy('occurred_on')
            ->get();

        foreach ($events as $eventData) {
            $event = $this->deserializeEvent($eventData);
            $this->handle($event);
        }
    }

    /**
     * Get read model statistics.
     */
    public function getStatistics(): array
    {
        return [
            'total_records' => DB::table('orderHistoryItem_read_models')->count(),
            'last_updated' => DB::table('orderHistoryItem_read_models')
                ->max('updated_at'),
        ];
    }

    /**
     * Deserialize stored event data.
     */
    private function deserializeEvent(\stdClass $eventData): DomainEvent
    {
        $eventClass = $eventData->event_type;
        $eventPayload = json_decode($eventData->payload, true);

        return $eventClass::fromPrimitives($eventPayload);
    }
}