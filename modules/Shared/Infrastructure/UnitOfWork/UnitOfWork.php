<?php

namespace Modules\Shared\Infrastructure\UnitOfWork;

use Modules\Shared\Application\Contracts\EventBus;
use Modules\Shared\Application\Contracts\UnitOfWork as UnitOfWorkContract;
use Modules\Shared\Domain\Contracts\DomainEvent;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Connection;
final class UnitOfWork implements UnitOfWorkContract
{
    /**
     * @var array<DomainEvent> Events collected during the unit of work
     */
    private array $collectedEvents = [];

    private bool $isActive = false;
    private ?Connection $connection = null;

    public function __construct(
        private readonly DatabaseManager $database,
        private readonly EventBus $eventBus
    ) {}

    /**
     * @throws \Throwable
     */
    public function begin(): void
    {
        if ($this->isActive) {
            throw new \RuntimeException('Unit of Work is already active');
        }

        $this->connection = $this->database->connection();
        $this->connection->beginTransaction();
        $this->isActive = true;
        $this->collectedEvents = [];
    }

    /**
     * @throws \Throwable
     */
    public function commit(): void
    {
        if (!$this->isActive) {
            throw new \RuntimeException('No active Unit of Work to commit');
        }

        try {
            // 1. Commit the database transaction first
            $this->connection->commit();

            // 2. If commit succeeds, dispatch all collected events
            $this->dispatchCollectedEvents();

        } catch (\Throwable $exception) {
            // If commit fails, rollback and clear events
            $this->rollback();
            throw $exception;
        } finally {
            $this->cleanup();
        }
    }

    /**
     * @throws \Throwable
     */
    public function rollback(): void
    {
        if (!$this->isActive) {
            return; // Nothing to rollback
        }

        try {
            $this->connection->rollBack();
        } finally {
            $this->cleanup();
        }
    }

    public function collectEvent(DomainEvent $event): void
    {
        if (!$this->isActive) {
            // If no active UoW, dispatch immediately
            $this->eventBus->publish($event);
            return;
        }

        $this->collectedEvents[] = $event;
    }

    public function getCollectedEvents(): array
    {
        return $this->collectedEvents;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function clearEvents(): void
    {
        $this->collectedEvents = [];
    }

    /**
     * Dispatch all collected events through the event bus.
     */
    private function dispatchCollectedEvents(): void
    {
        foreach ($this->collectedEvents as $event) {
            try {
                $this->eventBus->publish($event);
            } catch (\Throwable $exception) {
                // Log the error but don't stop other events
                logger()->error('Failed to dispatch domain event after commit', [
                    'event' => $event::class,
                    'event_id' => $event->eventId(),
                    'error' => $exception->getMessage()
                ]);
            }
        }
    }

    /**
     * Clean up the unit of work state.
     */
    private function cleanup(): void
    {
        $this->isActive = false;
        $this->connection = null;
        $this->collectedEvents = [];
    }
}
