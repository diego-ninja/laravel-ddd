<?php

namespace Modules\Sales\Domain\Events;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;

abstract class DomainEvent
{
    public readonly string $eventId;
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly string $aggregateId
    ) {
        $this->eventId = Uuid::uuid4()->toString();
        $this->occurredOn = new DateTimeImmutable();
    }

    /**
     * Get the event name.
     */
    abstract public function eventName(): string;

    /**
     * Get event data for serialization.
     */
    public function toPrimitives(): array
    {
        return [
            'eventId' => $this->eventId,
            'aggregateId' => $this->aggregateId,
            'occurredOn' => $this->occurredOn->format('Y-m-d H:i:s'),
        ];
    }
}