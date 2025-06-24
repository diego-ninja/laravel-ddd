<?php

namespace Modules\Shared\Domain\ValueObjects;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class AggregateId extends AbstractValueObject
{
    public static function fromUuid(UuidInterface|string $uuid): self
    {
        return is_string($uuid) ? self::fromString($uuid) : new AggregateId($uuid);
    }

    public function validate(): void
    {
        if (!Uuid::isValid($this->value)) {
            throw new \InvalidArgumentException('Invalid UUID format');
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid7());
    }

    public static function fromString(string $value): self
    {
        return new self(Uuid::fromString($value));
    }

    public function value(): UuidInterface
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value()->toString();
    }
}
