<?php

namespace Modules\Shared\Application\Contracts;

interface CommandHandlerInterface
{
    /**
     * Handle the command.
     */
    public function handle(CommandInterface $command): void;
}