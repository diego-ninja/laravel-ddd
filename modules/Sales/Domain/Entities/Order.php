<?php

namespace Modules\Sales\Domain\Entities;

use Modules\Sales\Domain\ValueObjects\OrderId;
use Modules\Sales\Domain\Exceptions\SalesDomainException;

class Order extends AggregateRoot
{
    private function __construct(
        private readonly OrderId $id,
        // Add other properties here
    ) {
    }

    /**
     * Create a new Order.
     */
    public static function create(
        OrderId $id,
        // Add other parameters here
    ): self {
        // Validate business rules here
        
        $order = new self(
            $id,
            // Pass other parameters here
        );

        // Record domain event
        // $order->record(new OrderWasCreated($id->value()));

        return $order;
    }

    /**
     * Get the order ID.
     */
    public function id(): OrderId
    {
        return $this->id;
    }

    /**
     * Update order information.
     */
    public function update(/* parameters */): void
    {
        // Validate business rules
        
        // Update properties
        
        // Record domain event if needed
        // $this->record(new OrderWasUpdated($this->id->value()));
    }

    /**
     * Check if order can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // Implement business rules for deletion
        return true;
    }

    /**
     * Delete the order.
     */
    public function delete(): void
    {
        if (!$this->canBeDeleted()) {
            throw SalesDomainException::businessRuleViolation('Order cannot be deleted');
        }

        // Record domain event
        // $this->record(new OrderWasDeleted($this->id->value()));
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            // Add other properties here
        ];
    }
}