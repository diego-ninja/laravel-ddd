<?php

namespace Modules\Users\Domain\ValueObjects;

use Modules\Shared\Domain\Contracts\ValueObject;
use Modules\Shared\Domain\ValueObjects\AbstractValueObject;
use InvalidArgumentException;

final readonly class UserEmail extends AbstractValueObject
{
    public function value(): string
    {
        return strtolower(trim((string) $this->value));
    }

    public function domain(): string
    {
        $normalized = $this->value();
        return substr($normalized, strpos($normalized, '@') + 1);
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value() === $other->value();
    }

    public function validate(): void
    {
        if (!is_string($this->value) || !filter_var($this->value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException(sprintf('Invalid email format: %s', $this->value));
        }
    }
}
