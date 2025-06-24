<?php

namespace Modules\Auth\Application\Commands;

use Illuminate\Auth\AuthenticationException;
use Modules\Auth\Application\DTO\JwtDTO;
use Modules\Shared\Application\Contracts\Command;
use Modules\Shared\Application\Contracts\CommandHandler;
use Modules\Shared\Application\DTOs\AbstractDTO;
use Tymon\JWTAuth\Facades\JWTAuth;

final readonly class LoginUserCommandHandler implements CommandHandler
{
    /**
     * @throws AuthenticationException
     */
    public function handle(Command $command): ?AbstractDTO
    {
        $credentials = [
            'email' => $command->email,
            'password' => $command->password,
        ];

        if (!$token = JWTAuth::attempt($credentials)) {
            throw new AuthenticationException('Invalid credentials');
        }

        return JwtDTO::from([
            'token' => $token,
            'tokenType' => 'bearer',
            'expiresIn' => auth('api')->factory()->getTTL() * 60,
        ]);
    }
}
