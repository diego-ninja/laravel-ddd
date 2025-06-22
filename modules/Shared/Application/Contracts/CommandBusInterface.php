<?php

namespace Modules\Shared\Application\Contracts;

interface CommandBusInterface
{
    /**
     * Dispatch a command.
     */
    public function dispatch(CommandInterface $command): void;

    /**
     * Register a command handler.
     */
    public function register(string $commandClass, string $handlerClass): void;

    /**
     * Add middleware to the command pipeline.
     */
    public function addMiddleware(string $middlewareClass): void;
}