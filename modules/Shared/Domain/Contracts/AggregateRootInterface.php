<?php

namespace Modules\Shared\Domain\Contracts;

interface AggregateRootInterface
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
}