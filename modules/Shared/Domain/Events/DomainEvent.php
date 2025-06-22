<?php

namespace Modules\Shared\Domain\Events;

use DateTimeImmutable;
use Modules\Shared\Domain\Contracts\DomainEvent as DomainEventContract;
use Ramsey\Uuid\Uuid;

abstract readonly class DomainEvent implements DomainEventContract
{
    public string $eventId;
    public DateTimeImmutable $occurredOn;

    public function __construct(
        public string $aggregateId
    ) {
        $this->eventId = Uuid::uuid4()->toString();
        $this->occurredOn = new DateTimeImmutable();
    }

    /**
     * Get the aggregate ID.
     */
    public function aggregateId(): string
    {
        return $this->aggregateId;
    }

    /**
     * Get the event ID.
     */
    public function eventId(): string
    {
        return $this->eventId;
    }

    /**
     * Get when the event occurred.
     */
    public function occurredOn(): DateTimeImmutable
    {
        return $this->occurredOn;
    }

    /**
     * Get the event name (must be implemented by concrete events).
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
            'eventName' => $this->eventName(),
        ];
    }

    /**
     * Create event from primitive data (must be implemented by concrete events).
     */
    abstract public static function fromPrimitives(array $data): self;
}
