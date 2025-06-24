<?php

namespace Modules\Shared\Infrastructure\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Shared\Application\Contracts\UnitOfWork;
use Modules\Shared\Domain\Entities\AggregateRoot;
use Modules\Shared\Domain\ValueObjects\Criteria;

abstract class AbstractRepository implements Repository
{
    public function __construct(
        protected readonly UnitOfWork $unitOfWork
    ) {}

    /**
     * Save an aggregate and collect its domain events.
     */
    public function save(AggregateRoot $aggregate): void
    {
        // 1. Persist the aggregate
        $this->doPersist($aggregate);

        // 2. Collect domain events for later dispatch
        $aggregate->dispatchRecordedEvents($this->unitOfWork);
    }

    /**
     * Generic implementation of findByCriteria using Eloquent.
     * Can be overridden in specific repositories for custom logic.
     */
    public function findByCriteria(Criteria $criteria): LengthAwarePaginator
    {
        $modelClass = $this->getModelClass();
        $queryBuilder = $modelClass::query();

        $this->applyGenericFilters($queryBuilder, $criteria);
        $this->applyGenericSorting($queryBuilder, $criteria);

        return $queryBuilder->paginate(
            perPage: $criteria->perPage,
            page: $criteria->page
        );
    }

    /**
     * Apply generic filters from criteria.
     */
    protected function applyGenericFilters(Builder $queryBuilder, Criteria $criteria): void
    {
        foreach ($criteria->filters as $field => $value) {
            if ($value !== null) {
                if (is_array($value)) {
                    $queryBuilder->whereIn($field, $value);
                } elseif (is_string($value) && str_contains($value, '%')) {
                    $queryBuilder->where($field, 'like', $value);
                } else {
                    $queryBuilder->where($field, $value);
                }
            }
        }
    }

    /**
     * Apply generic sorting from criteria.
     */
    protected function applyGenericSorting(Builder $queryBuilder, Criteria $criteria): void
    {
        $queryBuilder->orderBy($criteria->sort, $criteria->order);
    }

    /**
     * Get the Eloquent model class for this repository.
     * Must be implemented by concrete repositories.
     */
    abstract protected function getModelClass(): string;

    /**
     * Template method for actual persistence logic.
     */
    abstract protected function doPersist(AggregateRoot $aggregate): void;
}
