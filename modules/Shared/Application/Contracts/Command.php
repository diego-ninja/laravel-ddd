<?php

namespace Modules\Shared\Application\Contracts;

interface Command
{
    /**
     * Convert command to array for validation.
     */
    public function toArray(): array;
}
