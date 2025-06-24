<?php

namespace Modules\Shared\Application\Contracts;

interface QueryHandler
{
    /**
     * Handle the query.
     */
    public function handle(Query $query): mixed;
}
