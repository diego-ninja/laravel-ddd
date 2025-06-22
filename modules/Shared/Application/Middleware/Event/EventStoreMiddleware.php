<?php

namespace Modules\Shared\Application\Middleware\Event;

use Closure;
use Illuminate\Database\DatabaseManager;
use Modules\Shared\Application\Contracts\Middleware;
use Modules\Shared\Domain\Contracts\DomainEvent;

final readonly class EventStoreMiddleware implements Middleware
{
    public function __construct(
        private DatabaseManager $database
    ) {}

    public function handle(object $message, Closure $next): mixed
    {
        if ($message instanceof DomainEvent) {
            $this->storeEvent($message);
        }

        return $next($message);
    }

    private function storeEvent(DomainEvent $event): void
    {
        $this->database->table('domain_events')->insert([
            'id' => $event->eventId(),
            'aggregate_id' => $event->aggregateId(),
            'event_type' => $event::class,
            'event_data' => json_encode($this->extractEventData($event)),
            'occurred_at' => $event->occurredOn(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
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
