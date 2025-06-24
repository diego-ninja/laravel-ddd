<?php

namespace Modules\Shared\Domain\Contracts;

interface ValueObject
{
    public function equals(ValueObject $other): bool;
    public function validate(): void;
    public function value(): mixed;
}
