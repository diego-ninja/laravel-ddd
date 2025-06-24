<?php

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;
use Modules\Shared\Application\Contracts\QueryHandler;
use Modules\Shared\Application\Contracts\Query;
use Modules\Shared\Application\Contracts\QueryBus as QueryBusContract;
use Modules\Shared\Application\DTOs\AbstractDTO;

final class QueryBus extends AbstractBus implements QueryBusContract
{
    /**
     * @throws BindingResolutionException
     */
    public function ask(Query $query): ?AbstractDTO
    {
        return $this->executeWithMiddleware(
            $query,
            function (Query $query) {
                $handler = $this->resolveHandler($query);

                if (!$handler instanceof QueryHandler) {
                    throw new InvalidArgumentException(
                        "Handler must implement QueryHandler contract"
                    );
                }

                return $handler->handle($query);
            }
        );
    }
}
