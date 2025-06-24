<?php

namespace Modules\Shared\Application\Contracts;

use Modules\Shared\Application\DTOs\AbstractDTO;

interface CommandHandler
{
    /**
     * Handle the command.
     */
    public function handle(Command $command): ?AbstractDTO;
}
