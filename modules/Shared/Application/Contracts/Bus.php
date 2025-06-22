<?php

namespace Modules\Shared\Application\Contracts;

interface Bus
{
    /**
     * Register a handler for a specific message type.
     */
    public function register(string $messageClass, string $handlerClass): void;

    /**
     * Add middleware to the bus pipeline.
     *
     * @param class-string<Middleware> $middlewareClass
     */
    public function addMiddleware(string $middlewareClass): void;

    /**
     * Get registered middlewares.
     *
     * @return array<Middleware>
     */
    public function getMiddlewares(): array;

    /**
     * Get registered handlers.
     */
    public function getHandlers(): array;
}
