<?php

namespace Modules\Shared\Domain\Entities;

use Modules\Shared\Domain\Contracts\AggregateRootInterface;
use Modules\Shared\Domain\Contracts\DomainEventInterface;

abstract class AggregateRoot implements AggregateRootInterface
{
    private array $domainEvents = [];

    /**
     * Record a domain event.
     */
    protected function record(DomainEventInterface $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Get recorded domain events.
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        
        return $events;
    }

    /**
     * Clear domain events without returning them.
     */
    public function clearDomainEvents(): void
    {
        $this->domainEvents = [];
    }

    /**
     * Check if there are pending domain events.
     */
    public function hasDomainEvents(): bool
    {
        return count($this->domainEvents) > 0;
    }
}