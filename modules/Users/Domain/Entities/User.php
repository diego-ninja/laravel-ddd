<?php

namespace Modules\Users\Domain\Entities;

use Modules\Shared\Domain\Entities\AggregateRoot;
use Modules\Shared\Domain\ValueObjects\AggregateId;
use Modules\Users\Domain\Events\UserWasCreated;
use Modules\Users\Domain\Events\UserWasUpdated;
use Modules\Users\Domain\ValueObjects\UserEmail;
use Modules\Users\Domain\ValueObjects\UserName;

final class User extends AggregateRoot
{
    private AggregateId $id;
    private UserEmail $email;
    private UserName $name;
    private string $password;

    public function __construct(
        AggregateId $id,
        UserEmail $email,
        UserName $name,
        string $password
    ) {
        $this->id = $id;
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;

        $this->record(new UserWasCreated(
            (string) $this->id,
            $this->email->value(),
            $this->name->value(),
            $this->password,
            new \DateTimeImmutable()
        ));
    }

    public static function create(
        UserEmail $email,
        UserName $name,
        string $password
    ): self {
        return new self(
            AggregateId::generate(),
            $email,
            $name,
            $password
        );
    }

    public function id(): AggregateId
    {
        return $this->id;
    }

    public function email(): UserEmail
    {
        return $this->email;
    }

    public function name(): UserName
    {
        return $this->name;
    }

    public function password(): string
    {
        return $this->password;
    }

    public function changeEmail(UserEmail $email): void
    {
        $oldEmail = $this->email->value();
        $this->email = $email;

        $this->record(UserWasUpdated::fromSimpleChanges(
            (string) $this->id,
            [
                'email' => [
                    'old' => $oldEmail,
                    'new' => $email->value()
                ]
            ]
        ));
    }

    public function changeName(UserName $name): void
    {
        $oldName = $this->name->value();
        $this->name = $name;

        $this->record(UserWasUpdated::fromSimpleChanges(
            (string) $this->id,
            [
                'name' => [
                    'old' => $oldName,
                    'new' => $name->value()
                ]
            ]
        ));
    }

    public function updateProfile(UserEmail $email, UserName $name): void
    {
        $changes = [];

        if (!$this->email->equals($email)) {
            $changes['email'] = [
                'old' => $this->email->value(),
                'new' => $email->value()
            ];
            $this->email = $email;
        }

        if (!$this->name->equals($name)) {
            $changes['name'] = [
                'old' => $this->name->value(),
                'new' => $name->value()
            ];
            $this->name = $name;
        }

        if (!empty($changes)) {
            $this->record(UserWasUpdated::fromSimpleChanges(
                (string) $this->id,
                $changes
            ));
        }
    }
}
