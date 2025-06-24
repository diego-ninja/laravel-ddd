<?php

namespace Modules\Users\Application\Queries;

use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Shared\Application\Contracts\Query;
use Modules\Shared\Application\Contracts\QueryHandler;
use Modules\Users\Domain\Repositories\UserRepository;
use Modules\Users\Domain\ValueObjects\UserCriteria;

final readonly class GetUsersQueryHandler implements QueryHandler
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function handle(Query $query): LengthAwarePaginator
    {
        if (!$query instanceof GetUsersQuery) {
            throw new \InvalidArgumentException('Expected GetUsersQuery, got ' . get_class($query));
        }

        $criteria = new UserCriteria(
            search: $query->search,
            email: $query->email,
            active: $query->active,
            createdAfter: $query->createdAfter,
            createdBefore: $query->createdBefore,
            sort: $query->sort,
            order: $query->order,
            page: $query->page,
            perPage: $query->perPage
        );

        return $this->userRepository->findByCriteria($criteria);
    }
}
