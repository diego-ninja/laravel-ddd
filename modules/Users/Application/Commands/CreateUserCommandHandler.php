<?php

namespace Modules\Users\Application\Commands;

use Illuminate\Support\Facades\Hash;
use Modules\Shared\Application\Contracts\Command;
use Modules\Shared\Application\Contracts\CommandHandler;
use Modules\Shared\Application\DTOs\AbstractDTO;
use Modules\Users\Application\DTOs\UserDTO;
use Modules\Users\Domain\Entities\User;
use Modules\Users\Domain\Repositories\UserRepository;
use Modules\Users\Domain\ValueObjects\UserEmail;
use Modules\Users\Domain\ValueObjects\UserName;
use InvalidArgumentException;

final readonly class CreateUserCommandHandler implements CommandHandler
{
    public function __construct(private UserRepository $userRepository) {}

    public function handle(Command $command): ?AbstractDTO
    {
        if (!$command instanceof CreateUserCommand) {
            throw new InvalidArgumentException('Invalid command type');
        }

        $email = new UserEmail($command->email);
        $name = new UserName($command->name ?? $command->email);

        if ($this->userRepository->emailExists($email)) {
            throw new InvalidArgumentException('User with this email already exists');
        }

        $user = User::create(
            $email,
            $name,
            Hash::make($command->password)
        );

        $this->userRepository->save($user);

        return UserDTO::from([
            'id' => $user->id()->value(),
            'email' => $user->email()->value(),
            'name' => $user->name()->value(),
        ]);
    }
}
