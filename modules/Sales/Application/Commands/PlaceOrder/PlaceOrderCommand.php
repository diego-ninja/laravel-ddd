<?php

namespace Modules\Sales\Application\Commands\PlaceOrder;

readonly class PlaceOrderCommand
{
    public function __construct(
        // Add command properties here
        // public string $property,
    ) {
    }

    /**
     * Convert command to array for validation.
     */
    public function toArray(): array
    {
        return [
            // Map properties here
            // 'property' => $this->property,
        ];
    }

    /**
     * Create command from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            // Map data here
            // $data['property'],
        );
    }
}