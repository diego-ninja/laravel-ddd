<?php

namespace Modules\Shared\Domain\Entities;

use Modules\Shared\Application\Contracts\UnitOfWork;
use Modules\Shared\Domain\Contracts\AggregateRoot as AggregateRootContract;
use Modules\Shared\Domain\Contracts\DomainEvent;

abstract class AggregateRoot implements AggregateRootContract
{
    /**
     * @var array<DomainEvent>
     */
    private array $domainEvents = [];

    /**
     * Record a domain event.
     */
    protected function record(DomainEvent $event): void
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

    /**
     * Dispatch all recorded events through the Unit of Work.
     */
    public function dispatchRecordedEvents(UnitOfWork $unitOfWork): void
    {
        foreach ($this->domainEvents as $event) {
            $unitOfWork->collectEvent($event);
        }

        $this->clearDomainEvents();
    }
}
