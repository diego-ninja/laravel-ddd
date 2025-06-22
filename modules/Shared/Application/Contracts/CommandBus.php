<?php

namespace Modules\Shared\Application\Contracts;

interface CommandBus extends Bus
{
    /**
     * Dispatch a command.
     */
    public function dispatch(Command $command): void;
}
