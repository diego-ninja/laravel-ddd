<?php

namespace Modules\Shared\Domain\Contracts;

use DateTimeImmutable;

interface DomainEvent extends \JsonSerializable
{
    /**
     * Get the event name.
     */
    public function eventName(): string;

    /**
     * Get the aggregate ID.
     */
    public function aggregateId(): string;

    /**
     * Get the event ID.
     */
    public function eventId(): string;

    /**
     * Get when the event occurred.
     */
    public function occurredOn(): DateTimeImmutable;

    /**
     * Get event data for serialization.
     */
    public function array(): array;
}
