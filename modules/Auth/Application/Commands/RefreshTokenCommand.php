<?php

namespace Modules\Auth\Application\Commands;

use Modules\Shared\Application\Contracts\Command;

final readonly class RefreshTokenCommand implements Command
{
    public function __construct(
        public string $token
    ) {}

    public function toArray(): array
    {
        return [
            'token' => $this->token,
        ];
    }
}