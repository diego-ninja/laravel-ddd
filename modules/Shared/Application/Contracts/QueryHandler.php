<?php

namespace Modules\Shared\Application\Contracts;

interface QueryHandler
{
    /**
     * Handle the query.
     */
    public function handle(array $criteria = []): mixed;
}
