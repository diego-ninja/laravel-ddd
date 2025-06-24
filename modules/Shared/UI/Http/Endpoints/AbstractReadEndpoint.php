<?php

namespace Modules\Shared\UI\Http\Endpoints;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Modules\Shared\Application\Contracts\Query;
use Modules\Shared\Application\Contracts\QueryBus;
use Modules\Shared\UI\Http\Traits\ResponseTrait;

abstract class AbstractReadEndpoint extends Controller
{
    use ResponseTrait;

    public function __construct(
        protected QueryBus $queryBus
    ) {}

    /**
     * Execute a query and return response.
     */
    protected function executeQuery(Query $query): JsonResponse
    {
        try {
            $result = $this->queryBus->ask($query);
            return $this->success($result);
        } catch (\Exception $e) {
            return $this->error($e->getMessage());
        }
    }
}
