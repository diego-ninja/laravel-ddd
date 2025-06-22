<?php

namespace Modules\Shared\Application\Contracts;

interface QueryHandlerInterface
{
    /**
     * Handle the query.
     */
    public function handle(array $criteria = []): mixed;
}