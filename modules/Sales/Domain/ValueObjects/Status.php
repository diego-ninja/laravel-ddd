<?php

namespace Modules\Sales\Domain\ValueObjects;

use Modules\Sales\Domain\Exceptions\SalesDomainException;

readonly class Status
{
    // Define your enum values here
    private const VALID_VALUES = [
        'ACTIVE',
        'INACTIVE',
        // Add more values as needed
    ];

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
        return new self(strtoupper($value));
    }

    /**
     * Create ACTIVE instance.
     */
    public static function ACTIVE(): self
    {
        return new self('ACTIVE');
    }

    /**
     * Create INACTIVE instance.
     */
    public static function INACTIVE(): self
    {
        return new self('INACTIVE');
    }

    /**
     * Get all possible values.
     */
    public static function possibleValues(): array
    {
        return self::VALID_VALUES;
    }

    /**
     * Get the enum value.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Check if equals to another Status.
     */
    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Check if is active.
     */
    public function isActive(): bool
    {
        return $this->value === 'ACTIVE';
    }

    /**
     * Check if is inactive.
     */
    public function isInactive(): bool
    {
        return $this->value === 'INACTIVE';
    }

    /**
     * Convert to string.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Validate the enum value.
     */
    private function validate(string $value): void
    {
        if (empty($value)) {
            throw SalesDomainException::invalidData('Status cannot be empty');
        }

        if (!in_array($value, self::VALID_VALUES, true)) {
            throw SalesDomainException::invalidData(
                "Invalid Status value: {$value}. Valid values: " . implode(', ', self::VALID_VALUES)
            );
        }
    }
}