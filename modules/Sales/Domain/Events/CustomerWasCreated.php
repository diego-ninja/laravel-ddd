<?php

namespace Modules\Sales\Domain\Events;

class CustomerWasCreated extends DomainEvent
{
    public function __construct(
        string $aggregateId,
        // Add additional event data here
        // public readonly string $additionalData,
    ) {
        parent::__construct($aggregateId);
    }

    /**
     * Get the event name.
     */
    public function eventName(): string
    {
        return 'customer_was_created';
    }

    /**
     * Get event data for serialization.
     */
    public function toPrimitives(): array
    {
        return array_merge(parent::toPrimitives(), [
            // Add event-specific data here
            // 'additional_data' => $this->additionalData,
        ]);
    }

    /**
     * Create event from primitives.
     */
    public static function fromPrimitives(array $data): self
    {
        return new self(
            $data['aggregateId'],
            // Map additional data here
            // $data['additional_data']
        );
    }
}