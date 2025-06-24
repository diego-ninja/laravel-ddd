<?php

namespace Modules\Shared\Application\DTOs;

use Bag\Bag;
use JsonSerializable;

/**
 * Base class for all Data Transfer Objects using Bag for immutability.
 *
 * DTOs are used to transfer data between application layers
 * and should be immutable to prevent side effects.
 */
abstract readonly class AbstractDTO extends Bag implements JsonSerializable
{

    /**
     * Get a property value with optional default.
     */
    public function getValue(string $key, mixed $default = null): mixed
    {
        return $this->has($key) ? $this->get($key) : $default;
    }

    /**
     * Create a new instance with modified data.
     */
    public function with(mixed ...$values): static
    {
        // Handle array input for backward compatibility
        if (count($values) === 1 && is_array($values[0])) {
            $changes = $values[0];
        } else {
            $changes = $values;
        }

        $newData = array_merge($this->toArray(), $changes);
        return static::from($newData);
    }
}
