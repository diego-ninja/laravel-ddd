<?php

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;
use Modules\Shared\Application\Contracts\QueryHandler;
use Modules\Shared\Application\Contracts\Query;
use Modules\Shared\Application\Contracts\QueryBus as QueryBusContract;

final class QueryBus extends AbstractBus implements QueryBusContract
{
    /**
     * @throws BindingResolutionException
     */
    public function ask(Query $query): mixed
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

    public function handle(string $queryHandlerClass, array $criteria = []): mixed
    {
        // Legacy method - create a generic query and dispatch
        $query = new class($criteria) implements Query {
            public function __construct(public readonly array $criteria) {}
        };

        $this->register($query::class, $queryHandlerClass);
        return $this->ask($query);
    }
}
