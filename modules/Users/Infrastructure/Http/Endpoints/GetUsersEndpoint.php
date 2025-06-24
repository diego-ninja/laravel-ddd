<?php

namespace Modules\Users\Infrastructure\Http\Endpoints;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Shared\UI\Http\Endpoints\AbstractReadEndpoint;
use Modules\Users\Application\Queries\GetUsersQuery;

/**
 * Get paginated list of users.
 * 
 * Retrieves a paginated list of users with optional filtering and sorting capabilities. 
 * Supports search by name or email, filtering by various criteria, and flexible pagination.
 */
final class GetUsersEndpoint extends AbstractReadEndpoint
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $query = GetUsersQuery::fromRequestWithPagination($request);
            $result = $this->queryBus->ask($query);

            return $this->success([
                'users' => $result->items(),
                'pagination' => [
                    'current_page' => $result->currentPage(),
                    'per_page' => $result->perPage(),
                    'total' => $result->total(),
                    'last_page' => $result->lastPage(),
                    'from' => $result->firstItem(),
                    'to' => $result->lastItem(),
                ],
                'query_info' => [
                    'has_filters' => $query->hasFilters(),
                    'sort' => $query->sort,
                    'order' => $query->order,
                ]
            ]);

        } catch (\InvalidArgumentException $e) {
            return $this->error('Validation failed: ' . $e->getMessage(), 422);

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve users: ' . $e->getMessage());
        }
    }
}