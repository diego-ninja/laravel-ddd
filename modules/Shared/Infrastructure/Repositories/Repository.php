<?php

namespace Modules\Shared\Infrastructure\Repositories;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Shared\Domain\Entities\AggregateRoot;
use Modules\Shared\Domain\ValueObjects\AggregateId;
use Modules\Shared\Domain\ValueObjects\Criteria;

interface Repository
{
    public function save(AggregateRoot $aggregate): void;
    public function findById(AggregateId|string $id): ?AggregateRoot;
    public function findByCriteria(Criteria $criteria): LengthAwarePaginator;
}
