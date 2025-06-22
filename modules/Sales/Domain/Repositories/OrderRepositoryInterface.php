<?php

namespace Modules\Sales\Domain\Repositories;

use Modules\Sales\Domain\Entities\Order;
use Modules\Sales\Domain\ValueObjects\OrderId;

interface OrderRepositoryInterface
{
    /**
     * Find order by ID.
     */
    public function findById(OrderId $id): ?Order;

    /**
     * Get order by ID or fail.
     */
    public function getById(OrderId $id): Order;

    /**
     * Save order.
     */
    public function save(Order $order): void;

    /**
     * Delete order.
     */
    public function delete(Order $order): void;

    /**
     * Generate next identity.
     */
    public function nextIdentity(): OrderId;

    /**
     * Check if order exists.
     */
    public function exists(OrderId $id): bool;

    /**
     * Find all orders.
     */
    public function findAll(): array;

    /**
     * Find orders with pagination.
     */
    public function findPaginated(int $page = 1, int $perPage = 15): array;

    /**
     * Count total orders.
     */
    public function count(): int;

    /**
     * Find orders by criteria.
     * 
     * @param array $criteria
     * @return Order[]
     */
    public function findByCriteria(array $criteria): array;
}