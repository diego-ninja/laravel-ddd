<?php

namespace Modules\Shared\Infrastructure\Bus;

use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\Shared\Application\Contracts\Bus;
use Modules\Shared\Application\Contracts\Middleware;
use Illuminate\Container\Container;
use Illuminate\Pipeline\Pipeline;
use InvalidArgumentException;
abstract class AbstractBus implements Bus
{
    public function __construct(
        protected readonly Container $container,
        protected array $handlers = [],
        protected array $middlewares = []
    ) {
    }

    public function register(string $messageClass, string $handlerClass): void
    {
        $this->handlers[$messageClass] = $handlerClass;
    }

    public function addMiddleware(string $middlewareClass): void
    {
        if (!is_subclass_of($middlewareClass, Middleware::class)) {
            throw new InvalidArgumentException(
                "Middleware {$middlewareClass} must implement MiddlewareInterface"
            );
        }

        $this->middlewares[] = $middlewareClass;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function getHandlers(): array
    {
        return $this->handlers;
    }

    /**
     * Execute message through middleware pipeline.
     *
     * @throws BindingResolutionException
     */
    protected function executeWithMiddleware(object $message, Closure $finalHandler): mixed
    {
        return $this->container->make(Pipeline::class)
            ->send($message)
            ->through($this->resolveMiddlewares())
            ->then($finalHandler);
    }

    /**
     * Resolve middleware instances from the container.
     *
     * @throws BindingResolutionException
     */
    protected function resolveMiddlewares(): array
    {
        return array_map(
            fn(string $middlewareClass): Middleware =>
            $this->container->make($middlewareClass),
            $this->middlewares
        );
    }

    /**
     * Find and instantiate handler for the given message.
     *
     * @throws BindingResolutionException
     */
    protected function resolveHandler(object $message): object
    {
        $messageClass = $message::class;

        if (!isset($this->handlers[$messageClass])) {
            throw new InvalidArgumentException(
                "No handler registered for message: {$messageClass}"
            );
        }

        return $this->container->make($this->handlers[$messageClass]);
    }
}
