<?php

namespace Modules\Shared\Application\Contracts;

use Modules\Shared\Application\DTOs\AbstractDTO;

interface CommandBus extends Bus
{
    /**
     * Dispatch a command.
     */
    public function dispatch(Command $command): ?AbstractDTO;
}
