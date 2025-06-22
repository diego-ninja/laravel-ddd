<?php

namespace Modules\Sales\Domain\ValueObjects;

use Modules\Sales\Domain\Exceptions\SalesDomainException;

readonly class Email
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
     * Get the email value.
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Check if equals to another Email.
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
     * Get the domain part of the email.
     */
    public function domain(): string
    {
        return substr($this->value, strpos($this->value, '@') + 1);
    }

    /**
     * Get the local part of the email.
     */
    public function localPart(): string
    {
        return substr($this->value, 0, strpos($this->value, '@'));
    }

    /**
     * Check if the email belongs to a specific domain.
     */
    public function belongsToDomain(string $domain): bool
    {
        return strtolower($this->domain()) === strtolower($domain);
    }

    /**
     * Validate the email value.
     */
    private function validate(string $value): void
    {
        if (empty($value)) {
            throw SalesDomainException::invalidData('Email cannot be empty');
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw SalesDomainException::invalidData('Email must be a valid email address');
        }

        if (strlen($value) > 254) {
            throw SalesDomainException::invalidData('Email cannot exceed 254 characters');
        }
    }
}