<?php

namespace Modules\Sales\Infrastructure\Persistence;

use Modules\Sales\Domain\Entities\Customer;
use Modules\Sales\Domain\Repositories\CustomerRepositoryInterface;
use Modules\Sales\Domain\ValueObjects\CustomerId;
use Modules\Sales\Domain\Exceptions\SalesDomainException;
use Modules\Sales\Infrastructure\Persistence\Eloquent\CustomerModel;

class EloquentCustomerRepository implements CustomerRepositoryInterface
{
    public function __construct(
        private CustomerModel $model
    ) {
    }

    /**
     * Find customer by ID.
     */
    public function findById(CustomerId $id): ?Customer
    {
        $customerModel = $this->model->find($id->value());
        
        return $customerModel ? $this->toDomain($customerModel) : null;
    }

    /**
     * Get customer by ID or fail.
     */
    public function getById(CustomerId $id): Customer
    {
        $customer = $this->findById($id);
        
        if (!$customer) {
            throw SalesDomainException::notFound('Customer', $id->value());
        }
        
        return $customer;
    }

    /**
     * Save customer.
     */
    public function save(Customer $customer): void
    {
        $customerModel = $this->model->find($customer->id()->value());
        
        if ($customerModel) {
            $this->updateModel($customerModel, $customer);
        } else {
            $this->createModel($customer);
        }
    }

    /**
     * Delete customer.
     */
    public function delete(Customer $customer): void
    {
        $this->model->where('id', $customer->id()->value())->delete();
    }

    /**
     * Generate next identity.
     */
    public function nextIdentity(): CustomerId
    {
        return CustomerId::generate();
    }

    /**
     * Check if customer exists.
     */
    public function exists(CustomerId $id): bool
    {
        return $this->model->where('id', $id->value())->exists();
    }

    /**
     * Find all customers.
     */
    public function findAll(): array
    {
        return $this->model->all()
            ->map(fn($customerModel) => $this->toDomain($customerModel))
            ->toArray();
    }

    /**
     * Find customers with pagination.
     */
    public function findPaginated(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        $models = $this->model
            ->offset($offset)
            ->limit($perPage)
            ->get();
            
        return [
            'data' => $models->map(fn($customerModel) => $this->toDomain($customerModel))->toArray(),
            'total' => $this->count(),
            'page' => $page,
            'perPage' => $perPage,
            'hasMore' => $offset + $perPage < $this->count(),
        ];
    }

    /**
     * Count total customers.
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Find customers by criteria.
     */
    public function findByCriteria(array $criteria): array
    {
        $query = $this->model->newQuery();
        
        foreach ($criteria as $field => $value) {
            $query->where($field, $value);
        }
        
        return $query->get()
            ->map(fn($customerModel) => $this->toDomain($customerModel))
            ->toArray();
    }

    /**
     * Convert Eloquent model to domain entity.
     */
    private function toDomain(CustomerModel $customerModel): Customer
    {
        return Customer::create(
            CustomerId::fromString($customerModel->id),
            // Map other attributes here
        );
    }

    /**
     * Create new Eloquent model from domain entity.
     */
    private function createModel(Customer $customer): void
    {
        $this->model->create([
            'id' => $customer->id()->value(),
            // Map other attributes here
        ]);
    }

    /**
     * Update existing Eloquent model from domain entity.
     */
    private function updateModel(CustomerModel $customerModel, Customer $customer): void
    {
        $customerModel->update([
            // Map attributes here
        ]);
    }
}