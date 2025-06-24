<?php

namespace Modules\Auth\Application\Commands;

use Modules\Shared\Application\Contracts\Command;

final readonly class LoginUserCommand implements Command
{
    public function __construct(
        public string $email,
        public string $password
    ) {}

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}