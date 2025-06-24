<?php

declare(strict_types=1);

namespace Modules\Shared\Domain\ValueObjects;

use Modules\Shared\Domain\Contracts\ValueObject;

/**
 * Base Criteria value object for repository searches.
 *
 * Provides generic pagination and sorting capabilities.
 * Domain-specific criteria should extend this class.
 */
readonly class Criteria extends AbstractValueObject
{
    public function __construct(
        public string $sort = 'id',
        public string $order = 'asc',
        public int    $page = 1,
        public int    $perPage = 15,
        public array  $filters = [],
    ) {
        $this->validate();
    }

    public function validate(): void
    {
        if ($this->page < 1) {
            throw new \InvalidArgumentException('Page must be greater than 0');
        }

        if ($this->perPage < 1 || $this->perPage > 100) {
            throw new \InvalidArgumentException('PerPage must be between 1 and 100');
        }

        if (!in_array(strtolower($this->order), ['asc', 'desc'])) {
            throw new \InvalidArgumentException('Order must be "asc" or "desc"');
        }

        if (!in_array($this->sort, $this->getAllowedSortFields())) {
            throw new \InvalidArgumentException('Invalid sort field: ' . $this->sort);
        }
    }

    /**
     * Get allowed sort fields for this criteria.
     * Override in specific criteria classes.
     */
    protected function getAllowedSortFields(): array
    {
        return ['id', 'created_at', 'updated_at'];
    }

    /**
     * Check if criteria has any active filters.
     */
    public function hasFilters(): bool
    {
        return !empty($this->filters) || $this->hasSpecificFilters();
    }

    /**
     * Override in specific criteria to check domain-specific filters.
     */
    protected function hasSpecificFilters(): bool
    {
        return false;
    }

    /**
     * Get offset for pagination.
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /**
     * Get filter value by key.
     */
    public function getFilter(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }

    /**
     * Check if a specific filter exists and is not null.
     */
    public function hasFilter(string $key): bool
    {
        return array_key_exists($key, $this->filters) && $this->filters[$key] !== null;
    }

    public function equals(ValueObject $other): bool
    {
        return $other instanceof static
            && $this->sort === $other->sort
            && $this->order === $other->order
            && $this->page === $other->page
            && $this->perPage === $other->perPage
            && $this->filters === $other->filters;
    }

    public function toArray(): array
    {
        return [
            'sort' => $this->sort,
            'order' => $this->order,
            'page' => $this->page,
            'per_page' => $this->perPage,
            'filters' => $this->filters,
        ];
    }

    public function value(): array
    {
        return $this->toArray();
    }
}
