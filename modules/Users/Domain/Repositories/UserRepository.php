<?php

namespace Modules\Users\Domain\Repositories;

use Modules\Shared\Infrastructure\Repositories\Repository;
use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\ValueObjects\UserEmail;

interface UserRepository extends Repository
{
    public function findByEmail(UserEmail $email): ?User;

    public function emailExists(UserEmail $email): bool;

    // Note: findByCriteria is inherited from Repository interface
    // UserRepository can accept any Criteria (including UserCriteria)
}
