<?php

namespace Modules\Sales\Domain\ValueObjects;

use Modules\Sales\Domain\Exceptions\SalesDomainException;

readonly class ProductName
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
     * Get the string value.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Check if equals to another ProductName.
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
     * Check if the value is empty.
     */
    public function isEmpty(): bool
    {
        return empty(trim($this->value));
    }

    /**
     * Get the length of the value.
     */
    public function length(): int
    {
        return strlen($this->value);
    }

    /**
     * Validate the value.
     */
    private function validate(string $value): void
    {
        if (empty(trim($value))) {
            throw SalesDomainException::invalidData('ProductName cannot be empty');
        }

        if (strlen($value) > 255) {
            throw SalesDomainException::invalidData('ProductName cannot exceed 255 characters');
        }
    }
}