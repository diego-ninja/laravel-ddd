<?php

namespace Modules\Shared\Infrastructure\Listeners;

use Illuminate\Database\DatabaseManager;
use Modules\Shared\Domain\Contracts\DomainEvent;

final readonly class EventStoreListener
{
    public function __construct(
        private DatabaseManager $database
    ) {}

    public function handle($event): void
    {
        // Only handle DomainEvents, ignore other Laravel events
        if ($event instanceof DomainEvent) {
            $this->storeEvent($event);
        }
    }

    private function storeEvent(DomainEvent $event): void
    {
        $this->database->table('domain_events')->insert([
            'id' => $event->eventId(),
            'event_type' => $event::class,
            'aggregate_id' => $event->aggregateId(),
            'aggregate_type' => $this->extractAggregateType($event),
            'payload' => json_encode($this->extractEventData($event)),
            'occurred_on' => $event->occurredOn()->format('Y-m-d H:i:s'),
            'status' => 'pending',
            'version' => 1,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    private function extractAggregateType(DomainEvent $event): string
    {
        // Extract aggregate type from event class name
        // e.g., Modules\Users\Domain\Events\UserWasCreated -> User
        $eventClass = $event::class;
        $parts = explode('\\', $eventClass);
        
        // Find the module name (e.g., "Users")
        $moduleIndex = array_search('Domain', $parts);
        if ($moduleIndex !== false && $moduleIndex > 0) {
            return $parts[$moduleIndex - 1];
        }
        
        // Fallback: extract from event name
        $eventName = end($parts);
        if (preg_match('/^(\w+)Was/', $eventName, $matches)) {
            return $matches[1];
        }
        
        return 'Unknown';
    }

    private function extractEventData(DomainEvent $event): array
    {
        $reflection = new \ReflectionClass($event);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPublic()) {
                $data[$property->getName()] = $property->getValue($event);
            }
        }

        return $data;
    }
}