<?php

namespace Modules\Shared\Application\Contracts;

use InvalidArgumentException;
use Modules\Shared\Application\DTOs\AbstractDTO;

interface QueryBus extends Bus
{
    /**
     * Ask a query through the middleware pipeline.
     *
     * @param Query $query The query to execute
     * @return AbstractDTO|null The query result
     * @throws InvalidArgumentException If no handler is registered
     */
    public function ask(Query $query): ?AbstractDTO;
}
