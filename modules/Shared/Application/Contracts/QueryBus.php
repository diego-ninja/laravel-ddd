<?php

namespace Modules\Shared\Application\Contracts;

use InvalidArgumentException;

interface QueryBus extends Bus
{
    /**
     * Ask a query through the middleware pipeline.
     *
     * @param Query $query The query to execute
     * @return mixed The query result
     * @throws InvalidArgumentException If no handler is registered
     */
    public function ask(Query $query): mixed;

    /**
     * Handle a query using a specific handler class (legacy method).
     *
     * @param string $queryHandlerClass The handler class name
     * @param array $criteria Query criteria
     * @return mixed The query result
     * @deprecated Use ask() method with proper Query objects instead
     */
    public function handle(string $queryHandlerClass, array $criteria = []): mixed;
}
