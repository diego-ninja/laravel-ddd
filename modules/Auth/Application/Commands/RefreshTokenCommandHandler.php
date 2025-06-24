<?php

namespace Modules\Auth\Application\Commands;

use Modules\Auth\Application\DTO\JwtDTO;
use Modules\Shared\Application\Contracts\Command;
use Modules\Shared\Application\Contracts\CommandHandler;
use Modules\Shared\Application\DTOs\AbstractDTO;
use Tymon\JWTAuth\Facades\JWTAuth;

final readonly class RefreshTokenCommandHandler implements CommandHandler
{
    public function handle(Command $command): ?AbstractDTO
    {
        if (!$command instanceof RefreshTokenCommand) {
            throw new \InvalidArgumentException('Invalid command type');
        }

        $newToken = JWTAuth::setToken($command->token)->refresh();

        return JwtDTO::from([
            'token' => $newToken,
            'tokenType' => 'bearer',
            'expiresIn' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}