<?php

namespace Modules\Sales\Domain\ValueObjects;

use Modules\Sales\Domain\Exceptions\SalesDomainException;
use Ramsey\Uuid\Uuid;

readonly class OrderId
{
    private function __construct(
        private string $value
    ) {
        $this->validate($value);
    }

    /**
     * Create from string value.
     */
    public static function fromString(string $value): self
    {
        return new self($value);
    }

    /**
     * Generate a new unique ID.
     */
    public static function generate(): self
    {
        return new self(Uuid::uuid4()->toString());
    }

    /**
     * Get the string value.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Check if equals to another OrderId.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Convert to string.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the ID value.
     */
    private function validate(string $value): void
    {
        if (empty($value)) {
            throw SalesDomainException::invalidData('Order ID cannot be empty');
        }

        if (!Uuid::isValid($value)) {
            throw SalesDomainException::invalidData('Order ID must be a valid UUID');
        }
    }
}