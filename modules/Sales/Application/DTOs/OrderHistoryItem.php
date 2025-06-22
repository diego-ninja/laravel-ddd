<?php

namespace Modules\Sales\Application\DTOs;

use DateTimeImmutable;

readonly class OrderHistoryItem
{
    public function __construct(
        public string $id,
        // Add read model properties here
        // public string $name,
        // public string $status,
        // public float $amount,
        public DateTimeImmutable $createdAt,
        // public ?DateTimeImmutable $updatedAt = null,
    ) {
    }

    /**
     * Create read model from array data.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            // Map array data to properties
            // name: $data['name'],
            // status: $data['status'],
            // amount: (float) $data['amount'],
            createdAt: new DateTimeImmutable($data['created_at']),
            // updatedAt: isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
        );
    }

    /**
     * Convert read model to array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            // Add property mappings
            // 'name' => $this->name,
            // 'status' => $this->status,
            // 'amount' => $this->amount,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            // 'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Convert to JSON.
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Create from domain entity (for projectors).
     */
    public static function fromDomainEntity(object $entity): self
    {
        return new self(
            id: $entity->id()->value(),
            // Map domain entity properties
            // name: $entity->getName(),
            // status: $entity->getStatus()->value(),
            // amount: $entity->getPrice()->amountAsFloat(),
            createdAt: new DateTimeImmutable(), // or $entity->getCreatedAt()
        );
    }

    /**
     * Get formatted display data.
     */
    public function getDisplayData(): array
    {
        return [
            'id' => $this->id,
            // Add formatted fields for UI
            // 'display_name' => ucfirst($this->name),
            // 'formatted_amount' => number_format($this->amount, 2),
            // 'human_date' => $this->createdAt->format('M j, Y'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}