<?php

namespace Modules\Shared\Domain\Events;

use DateTimeImmutable;
use Illuminate\Foundation\Events\Dispatchable;
use Modules\Shared\Domain\Contracts\DomainEvent as DomainEventContract;
use Ramsey\Uuid\Uuid;

abstract readonly class DomainEvent implements DomainEventContract
{
    use Dispatchable;

    public readonly string $eventId;
    public readonly DateTimeImmutable $occurredOn;

    public function __construct(
        public readonly string $aggregateId
    ) {
        $this->eventId = Uuid::uuid4()->toString();
        $this->occurredOn = new DateTimeImmutable();
    }

    public function name(): string
    {
        return static::class;
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
     * Get event data for serialization.
     */
    public function array(): array
    {
        return [
            'eventId' => $this->eventId,
            'aggregateId' => $this->aggregateId,
            'occurredOn' => $this->occurredOn->format('Y-m-d H:i:s'),
            'eventName' => $this->name(),
        ];
    }

    /**
     * JsonSerializable implementation.
     */
    public function jsonSerialize(): array
    {
        return $this->array();
    }
}
