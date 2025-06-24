<?php

namespace Modules\Auth\Application\Commands;

use Modules\Shared\Application\Contracts\Command;
use Modules\Shared\Application\Contracts\CommandHandler;
use Modules\Shared\Application\DTOs\AbstractDTO;
use Tymon\JWTAuth\Facades\JWTAuth;

final readonly class LogoutUserCommandHandler implements CommandHandler
{
    public function handle(Command $command): ?AbstractDTO
    {
        if (!$command instanceof LogoutUserCommand) {
            throw new \InvalidArgumentException('Invalid command type');
        }

        JWTAuth::setToken($command->token)->invalidate();

        return null;
    }
}