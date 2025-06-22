<?php

namespace Modules\Sales\Domain\Repositories;

use Modules\Sales\Domain\Entities\Customer;
use Modules\Sales\Domain\ValueObjects\CustomerId;

interface CustomerRepositoryInterface
{
    /**
     * Find customer by ID.
     */
    public function findById(CustomerId $id): ?Customer;

    /**
     * Get customer by ID or fail.
     */
    public function getById(CustomerId $id): Customer;

    /**
     * Save customer.
     */
    public function save(Customer $customer): void;

    /**
     * Delete customer.
     */
    public function delete(Customer $customer): void;

    /**
     * Generate next identity.
     */
    public function nextIdentity(): CustomerId;

    /**
     * Check if customer exists.
     */
    public function exists(CustomerId $id): bool;

    /**
     * Find all customers.
     */
    public function findAll(): array;

    /**
     * Find customers with pagination.
     */
    public function findPaginated(int $page = 1, int $perPage = 15): array;

    /**
     * Count total customers.
     */
    public function count(): int;

    /**
     * Find customers by criteria.
     * 
     * @param array $criteria
     * @return Customer[]
     */
    public function findByCriteria(array $criteria): array;
}