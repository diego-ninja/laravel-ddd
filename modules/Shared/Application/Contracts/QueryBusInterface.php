<?php

namespace Modules\Shared\Application\Contracts;

interface QueryBusInterface
{
    /**
     * Handle a query.
     */
    public function handle(string $queryHandlerClass, array $criteria = []): mixed;

    /**
     * Register a query handler.
     */
    public function register(string $queryClass, string $handlerClass): void;

    /**
     * Add middleware to the query pipeline.
     */
    public function addMiddleware(string $middlewareClass): void;
}