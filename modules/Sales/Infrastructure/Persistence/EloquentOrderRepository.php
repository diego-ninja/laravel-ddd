<?php

namespace Modules\Sales\Infrastructure\Persistence;

use Modules\Sales\Domain\Entities\Order;
use Modules\Sales\Domain\Repositories\OrderRepositoryInterface;
use Modules\Sales\Domain\ValueObjects\OrderId;
use Modules\Sales\Domain\Exceptions\SalesDomainException;
use Modules\Sales\Infrastructure\Persistence\Eloquent\OrderModel;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function __construct(
        private OrderModel $model
    ) {
    }

    /**
     * Find order by ID.
     */
    public function findById(OrderId $id): ?Order
    {
        $orderModel = $this->model->find($id->value());
        
        return $orderModel ? $this->toDomain($orderModel) : null;
    }

    /**
     * Get order by ID or fail.
     */
    public function getById(OrderId $id): Order
    {
        $order = $this->findById($id);
        
        if (!$order) {
            throw SalesDomainException::notFound('Order', $id->value());
        }
        
        return $order;
    }

    /**
     * Save order.
     */
    public function save(Order $order): void
    {
        $orderModel = $this->model->find($order->id()->value());
        
        if ($orderModel) {
            $this->updateModel($orderModel, $order);
        } else {
            $this->createModel($order);
        }
    }

    /**
     * Delete order.
     */
    public function delete(Order $order): void
    {
        $this->model->where('id', $order->id()->value())->delete();
    }

    /**
     * Generate next identity.
     */
    public function nextIdentity(): OrderId
    {
        return OrderId::generate();
    }

    /**
     * Check if order exists.
     */
    public function exists(OrderId $id): bool
    {
        return $this->model->where('id', $id->value())->exists();
    }

    /**
     * Find all orders.
     */
    public function findAll(): array
    {
        return $this->model->all()
            ->map(fn($orderModel) => $this->toDomain($orderModel))
            ->toArray();
    }

    /**
     * Find orders with pagination.
     */
    public function findPaginated(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        $models = $this->model
            ->offset($offset)
            ->limit($perPage)
            ->get();
            
        return [
            'data' => $models->map(fn($orderModel) => $this->toDomain($orderModel))->toArray(),
            'total' => $this->count(),
            'page' => $page,
            'perPage' => $perPage,
            'hasMore' => $offset + $perPage < $this->count(),
        ];
    }

    /**
     * Count total orders.
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Find orders by criteria.
     */
    public function findByCriteria(array $criteria): array
    {
        $query = $this->model->newQuery();
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->get()
            ->map(fn($orderModel) => $this->toDomain($orderModel))
            ->toArray();
    }

    /**
     * Convert Eloquent model to domain entity.
     */
    private function toDomain(OrderModel $orderModel): Order
    {
        return Order::create(
            OrderId::fromString($orderModel->id),
            // Map other attributes here
        );
    }

    /**
     * Create new Eloquent model from domain entity.
     */
    private function createModel(Order $order): void
    {
        $this->model->create([
            'id' => $order->id()->value(),
            // Map other attributes here
        ]);
    }

    /**
     * Update existing Eloquent model from domain entity.
     */
    private function updateModel(OrderModel $orderModel, Order $order): void
    {
        $orderModel->update([
            // Map attributes here
        ]);
    }
}