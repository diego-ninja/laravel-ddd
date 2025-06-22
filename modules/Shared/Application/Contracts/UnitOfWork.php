<?php

namespace Modules\Shared\Application\Contracts;

use Modules\Shared\Domain\Contracts\DomainEvent;

interface UnitOfWork
{
    /**
     * Start a new unit of work.
     */
    public function begin(): void;

    /**
     * Commit the unit of work and dispatch all collected events.
     */
    public function commit(): void;

    /**
     * Rollback the unit of work and discard all collected events.
     */
    public function rollback(): void;

    /**
     * Add a domain event to be dispatched after commit.
     */
    public function collectEvent(DomainEvent $event): void;

    /**
     * Get all collected events.
     *
     * @return array<DomainEvent>
     */
    public function getCollectedEvents(): array;

    /**
     * Check if we're currently in a unit of work.
     */
    public function isActive(): bool;

    /**
     * Clear all collected events without dispatching them.
     */
    public function clearEvents(): void;
}
