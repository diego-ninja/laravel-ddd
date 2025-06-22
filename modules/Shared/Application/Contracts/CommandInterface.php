<?php

namespace Modules\Shared\Application\Contracts;

interface CommandInterface
{
    /**
     * Convert command to array for validation.
     */
    public function toArray(): array;
}