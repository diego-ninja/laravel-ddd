<?php

namespace Modules\Shared\Domain\ValueObjects;

abstract class BaseValueObject
{
    /**
     * Check if equals to another value object.
     */
    abstract public function equals(self $other): bool;

    /**
     * Convert to string representation.
     */
    abstract public function __toString(): string;

    /**
     * Convert to array (useful for serialization).
     */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    /**
     * Convert to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}