<?php

namespace Modules\Users\Application\DTOs;

use Modules\Shared\Application\DTOs\AbstractDTO;

final readonly class UserDTO extends AbstractDTO
{
    public function __construct(
        public string $id,
        public string $email,
        public string $name,
    ) {
    }
}
