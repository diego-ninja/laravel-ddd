<?php

namespace Modules\Shared\Domain\Contracts;

interface RepositoryInterface
{
    /**
     * Save an entity.
     */
    public function save(object $entity): void;

    /**
     * Delete an entity.
     */
    public function delete(object $entity): void;

    /**
     * Check if entity exists.
     */
    public function exists(object $id): bool;

    /**
     * Count total entities.
     */
    public function count(): int;
}