<?php

namespace Modules\Users\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Shared\Application\Contracts\UnitOfWork;
use Modules\Shared\Domain\Entities\AggregateRoot;
use Modules\Shared\Domain\ValueObjects\AggregateId;
use Modules\Shared\Domain\ValueObjects\Criteria;
use Modules\Shared\Infrastructure\Repositories\AbstractRepository;
use Modules\Shared\Infrastructure\Transformers\DomainEntityTransformer;
use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\Repositories\UserRepository;
use Modules\Users\Domain\ValueObjects\UserEmail;
use Modules\Users\Domain\ValueObjects\UserCriteria;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\User as UserModel;

final class EloquentUserRepository extends AbstractRepository implements UserRepository
{
    private DomainEntityTransformer $transformer;

    public function __construct(
        UnitOfWork $unitOfWork,
        DomainEntityTransformer $transformer = null
    ) {
        parent::__construct($unitOfWork);

        if ($transformer === null) {
            $transformer = new DomainEntityTransformer();
            $transformer->setCustomMappings([
                User::class => [
                    'id' => fn(UserModel $model) => AggregateId::fromString($model->id)
                ]
            ]);
        }
        $this->transformer = $transformer;
    }

    public function findById(AggregateId|string $id): ?AggregateRoot
    {
        $idString = $id instanceof AggregateId ? (string) $id : $id;
        $userModel = UserModel::find($idString);

        return $userModel ? $this->transformer->toDomainEntity($userModel, User::class) : null;
    }

    public function findByEmail(UserEmail $email): ?User
    {
        $userModel = UserModel::where('email', $email->value())->first();

        return $userModel ? $this->transformer->toDomainEntity($userModel, User::class) : null;
    }

    public function emailExists(UserEmail $email): bool
    {
        return UserModel::where('email', $email->value())->exists();
    }

    /**
     * Override findByCriteria to add User-specific filtering logic.
     * Uses the base implementation but adds custom search logic.
     */
    public function findByCriteria(Criteria $criteria): LengthAwarePaginator
    {
        // If it's UserCriteria, apply custom filtering
        if ($criteria instanceof UserCriteria) {
            return $this->findByUserSearchCriteria($criteria);
        }

        // Otherwise, use the generic implementation from AbstractRepository
        return parent::findByCriteria($criteria);
    }

    /**
     * Apply User-specific search logic.
     */
    private function findByUserSearchCriteria(UserCriteria $criteria): LengthAwarePaginator
    {
        $queryBuilder = UserModel::query();

        // Apply User-specific filters first
        $this->applyUserSpecificFilters($queryBuilder, $criteria);

        // Then apply generic filters and sorting
        $this->applyGenericFilters($queryBuilder, $criteria);
        $this->applyGenericSorting($queryBuilder, $criteria);

        return $queryBuilder->paginate(
            perPage: $criteria->perPage,
            page: $criteria->page
        );
    }

    /**
     * Apply User-specific filters that can't be handled generically.
     */
    private function applyUserSpecificFilters(Builder $queryBuilder, UserCriteria $criteria): void
    {
        // Complex search across multiple fields
        if ($criteria->hasSearch()) {
            $queryBuilder->where(function (Builder $q) use ($criteria) {
                $q->where('name', 'like', "%{$criteria->search}%")
                  ->orWhere('email', 'like', "%{$criteria->search}%");
            });
        }

        // Email-specific filtering with LIKE
        if ($criteria->hasEmailFilter()) {
            $queryBuilder->where('email', 'like', "%{$criteria->email}%");
        }
    }

    /**
     * Get the Eloquent model class for generic operations.
     */
    protected function getModelClass(): string
    {
        return UserModel::class;
    }

    protected function doPersist(AggregateRoot $aggregate): void
    {
        $data = $this->transformer->toArray($aggregate);

        UserModel::updateOrCreate(
            ['id' => $data['id']],
            $data
        );
    }
}
