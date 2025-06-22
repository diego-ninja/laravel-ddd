<?php

namespace Modules\Sales\Domain\Entities;

use Modules\Sales\Domain\ValueObjects\CustomerId;
use Modules\Sales\Domain\Exceptions\SalesDomainException;

class Customer extends AggregateRoot
{
    private function __construct(
        private readonly CustomerId $id,
        // Add other properties here
    ) {
    }

    /**
     * Create a new Customer.
     */
    public static function create(
        CustomerId $id,
        // Add other parameters here
    ): self {
        // Validate business rules here
        
        $customer = new self(
            $id,
            // Pass other parameters here
        );

        // Record domain event
        // $customer->record(new CustomerWasCreated($id->value()));

        return $customer;
    }

    /**
     * Get the customer ID.
     */
    public function id(): CustomerId
    {
        return $this->id;
    }

    /**
     * Update customer information.
     */
    public function update(/* parameters */): void
    {
        // Validate business rules
        
        // Update properties
        
        // Record domain event if needed
        // $this->record(new CustomerWasUpdated($this->id->value()));
    }

    /**
     * Check if customer can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // Implement business rules for deletion
        return true;
    }

    /**
     * Delete the customer.
     */
    public function delete(): void
    {
        if (!$this->canBeDeleted()) {
            throw SalesDomainException::businessRuleViolation('Customer cannot be deleted');
        }

        // Record domain event
        // $this->record(new CustomerWasDeleted($this->id->value()));
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