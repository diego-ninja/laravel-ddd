<?php

namespace Modules\Shared\Application\Middleware\Command;

use Modules\Shared\Application\Contracts\Middleware;
use Modules\Shared\Application\Contracts\UnitOfWork;
use Throwable;

final readonly class UnitOfWorkMiddleware implements Middleware
{
    public function __construct(
        private UnitOfWork $unitOfWork
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(object $message, \Closure $next): mixed
    {
        // Check if UoW is already active (nested commands)
        if ($this->unitOfWork->isActive()) {
            // If already in a UoW, just continue without starting a new one
            return $next($message);
        }

        // Start new Unit of Work
        $this->unitOfWork->begin();

        try {
            // Execute the command handler
            $result = $next($message);

            // If everything went well, commit the transaction and dispatch events
            $this->unitOfWork->commit();

            return $result;

        } catch (Throwable $exception) {
            // If anything fails, rollback everything and discard events
            $this->unitOfWork->rollback();
            throw $exception;
        }
    }
}
