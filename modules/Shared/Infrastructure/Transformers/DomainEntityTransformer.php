<?php

namespace Modules\Shared\Infrastructure\Transformers;

use Illuminate\Database\Eloquent\Model;
use Modules\Shared\Domain\Entities\AggregateRoot;
use ReflectionClass;
use ReflectionException;
use InvalidArgumentException;

final class DomainEntityTransformer extends BaseTransformer
{
    public function toDomainEntity(Model $model, string $entityClass): AggregateRoot
    {
        try {
            $reflection = new ReflectionClass($entityClass);

            if (!$reflection->isSubclassOf(AggregateRoot::class)) {
                throw new InvalidArgumentException("Class {$entityClass} must extend AggregateRoot");
            }

            $constructor = $reflection->getConstructor();

            if (!$constructor) {
                throw new InvalidArgumentException("Entity {$entityClass} must have a constructor");
            }

            $parameters = $constructor->getParameters();
            $arguments = $this->buildConstructorArguments($model, $parameters, $entityClass);

            return $reflection->newInstanceArgs($arguments);

        } catch (ReflectionException $e) {
            throw new InvalidArgumentException("Failed to create entity {$entityClass}: " . $e->getMessage());
        }
    }

    public function toArray(AggregateRoot $entity): array
    {
        $reflection = new ReflectionClass($entity);
        $data = [];

        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            $property->setAccessible(true);
            $value = $property->getValue($entity);
            $fieldName = $this->getFieldName($property->getName());

            $data[$fieldName] = $this->convertValueForPersistence($value);
        }

        return $data;
    }

    protected function hasValue(mixed $source, string $key): bool
    {
        /** @var Model $source */
        $fieldVariations = [
            $key,
            $this->toSnakeCase($key),
            $this->camelToSnake($key),
        ];

        foreach ($fieldVariations as $field) {
            if ($source->offsetExists($field) || isset($source->attributes[$field])) {
                return true;
            }
        }

        return false;
    }

    protected function getValue(mixed $source, string $key): mixed
    {
        /** @var Model $source */
        $fieldVariations = [
            $key,
            $this->toSnakeCase($key),
            $this->camelToSnake($key),
        ];

        foreach ($fieldVariations as $field) {
            if ($source->offsetExists($field)) {
                return $source->offsetGet($field);
            }

            if (isset($source->attributes[$field])) {
                return $source->attributes[$field];
            }
        }

        return null;
    }
}
