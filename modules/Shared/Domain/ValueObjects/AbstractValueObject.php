<?php

namespace Modules\Shared\Domain\ValueObjects;

use Bag\Bag;
use Stringable;

abstract readonly class AbstractValueObject extends Bag implements Stringable
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
}
