<?php

namespace Modules\Auth\Application\DTO;

use Modules\Shared\Application\DTOs\AbstractDTO;

final readonly class JwtDTO extends AbstractDTO
{
    public function __construct(
        public string $token,
        public string $tokenType,
        public int $expiresIn
    ) {
    }
}
