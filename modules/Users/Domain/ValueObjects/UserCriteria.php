<?php

declare(strict_types=1);

namespace Modules\Users\Domain\ValueObjects;

use Modules\Shared\Domain\ValueObjects\Criteria;

/**
 * User-specific search criteria that extends base Criteria.
 * Provides specialized filtering capabilities for User entities.
 */
final readonly class UserCriteria extends Criteria
{
    public function __construct(
        public ?string $search = null,
        public ?string $email = null,
        public ?bool $active = null,
        public ?string $createdAfter = null,
        public ?string $createdBefore = null,
        string $sort = 'id',
        string $order = 'asc',
        int $page = 1,
        int $perPage = 15,
    ) {
        // Prepare generic filters for base class
        $filters = array_filter([
            'active' => $active,
            'created_after' => $createdAfter,
            'created_before' => $createdBefore,
        ], fn($value) => $value !== null);

        parent::__construct(
            sort: $sort,
            order: $order,
            page: $page,
            perPage: $perPage,
            filters: $filters
        );
    }

    /**
     * Override to include User-specific allowed sort fields.
     */
    protected function getAllowedSortFields(): array
    {
        return ['id', 'email', 'name', 'created_at', 'updated_at'];
    }

    /**
     * Override to check User-specific filters.
     */
    protected function hasSpecificFilters(): bool
    {
        return $this->search !== null || $this->email !== null;
    }

    /**
     * Check if search filter is active.
     */
    public function hasSearch(): bool
    {
        return $this->search !== null && trim($this->search) !== '';
    }

    /**
     * Check if email filter is active.
     */
    public function hasEmailFilter(): bool
    {
        return $this->email !== null && trim($this->email) !== '';
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'search' => $this->search,
            'email' => $this->email,
            'active' => $this->active,
            'created_after' => $this->createdAfter,
            'created_before' => $this->createdBefore,
        ]);
    }

    public function value(): array
    {
        return $this->toArray();
    }
}
