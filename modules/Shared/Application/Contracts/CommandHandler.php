<?php

namespace Modules\Shared\Application\Contracts;

interface CommandHandler
{
    /**
     * Handle the command.
     */
    public function handle(Command $command): void;
}
