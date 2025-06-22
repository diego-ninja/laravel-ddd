<?php

namespace Modules\Shared\Infrastructure\Repositories;

use Modules\Shared\Application\Contracts\UnitOfWork;
use Modules\Shared\Domain\Entities\AggregateRoot;
abstract class AbstractRepository
{
    public function __construct(
        protected readonly UnitOfWork $unitOfWork
    ) {}

    /**
     * Save an aggregate and collect its domain events.
     */
    protected function saveAggregate(AggregateRoot $aggregate): void
    {
        // 1. Persist the aggregate
        $this->doPersist($aggregate);

        // 2. Collect domain events for later dispatch
        $aggregate->dispatchRecordedEvents($this->unitOfWork);
    }

    /**
     * Template method for actual persistence logic.
     */
    abstract protected function doPersist(AggregateRoot $aggregate): void;
}
