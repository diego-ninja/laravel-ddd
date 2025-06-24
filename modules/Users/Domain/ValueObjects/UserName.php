<?php

namespace Modules\Users\Domain\ValueObjects;

use Modules\Shared\Domain\Contracts\ValueObject;
use Modules\Shared\Domain\ValueObjects\AbstractValueObject;
use InvalidArgumentException;

final readonly class UserName extends AbstractValueObject
{
    public function value(): string
    {
        return trim((string) $this->value);
    }

    public function __toString(): string
    {
        return $this->value();
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value() === $other->value();
    }

    public function validate(): void
    {
        $this->ensureIsNotEmpty($this->value);
        $this->ensureIsValidLength($this->value);
    }

    private function ensureIsNotEmpty(mixed $value): void
    {
        if (!is_string($value) || empty(trim($value))) {
            throw new InvalidArgumentException('User name cannot be empty');
        }
    }

    private function ensureIsValidLength(mixed $value): void
    {
        if (!is_string($value)) {
            throw new InvalidArgumentException('User name must be a string');
        }

        $length = strlen(trim($value));
        if ($length < 2 || $length > 255) {
            throw new InvalidArgumentException('User name must be between 2 and 255 characters');
        }
    }
}
