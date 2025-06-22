<?php

namespace Modules\Shared\Domain\Contracts;

use Modules\Shared\Application\Contracts\UnitOfWork;

interface AggregateRoot
{
    /**
     * Get recorded domain events.
     */
    public function pullDomainEvents(): array;

    /**
     * Clear domain events without returning them.
     */
    public function clearDomainEvents(): void;

    /**
     * Check if there are pending domain events.
     */
    public function hasDomainEvents(): bool;

    /**
     * Dispatch all recorded events through the Unit of Work.
     */
    public function dispatchRecordedEvents(UnitOfWork $unitOfWork): void;
}
