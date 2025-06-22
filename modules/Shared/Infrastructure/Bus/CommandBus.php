<?php

namespace Modules\Shared\Infrastructure\Bus;

use Illuminate\Contracts\Container\BindingResolutionException;
use InvalidArgumentException;
use Modules\Shared\Application\Contracts\CommandBus as CommandBusContract;
use Modules\Shared\Application\Contracts\CommandHandler;
use Modules\Shared\Application\Contracts\Command;

final class CommandBus extends AbstractBus implements CommandBusContract
{
    /**
     * @throws BindingResolutionException
     */
    public function dispatch(Command $command): void
    {
        $this->executeWithMiddleware(
            $command,
            function (Command $command) {
                $handler = $this->resolveHandler($command);

                if (!$handler instanceof CommandHandler) {
                    throw new InvalidArgumentException(
                        "Handler must implement CommandHandler contract"
                    );
                }

                $handler->handle($command);
            }
        );
    }}
