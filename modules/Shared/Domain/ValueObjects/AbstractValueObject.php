<?php

namespace Modules\Shared\Domain\ValueObjects;

use Bag\Bag;
use Modules\Shared\Domain\Contracts\ValueObject;
use Stringable;

abstract readonly class AbstractValueObject implements ValueObject
{
    public function __construct(protected mixed $value)
    {
        $this->validate($value);
    }

    /**
     * Check if equals to another value object.
     */
    public function equals(ValueObject $other): bool
    {
        return $other instanceof self && $this->value === $other->value;
    }

    /**
     * Validate value.
     *
     * @throws \InvalidArgumentException
     */
    abstract public function validate(): void;
}
