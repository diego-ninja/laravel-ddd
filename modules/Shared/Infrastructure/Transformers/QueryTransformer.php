<?php

namespace Modules\Shared\Infrastructure\Transformers;

use Illuminate\Http\Request;
use Modules\Shared\Application\Contracts\Query;
use ReflectionClass;
use ReflectionException;
use InvalidArgumentException;

final class QueryTransformer extends BaseTransformer
{
    protected function hasValue(mixed $source, string $key): bool
    {
        /** @var Request $source */
        $fieldVariations = [
            $key,
            $this->toSnakeCase($key),
            $this->camelToSnake($key),
        ];

        foreach ($fieldVariations as $field) {
            if ($source->has($field)) {
                return true;
            }
        }

        return false;
    }

    protected function getValue(mixed $source, string $key): mixed
    {
        /** @var Request $source */
        $fieldVariations = [
            $key,
            $this->toSnakeCase($key),
            $this->camelToSnake($key),
        ];

        foreach ($fieldVariations as $field) {
            if ($source->has($field)) {
                return $source->input($field);
            }
        }

        return null;
    }

    public function fromRequest(Request $request, string $queryClass): Query
    {
        try {
            $reflection = new ReflectionClass($queryClass);

            if (!$reflection->implementsInterface(Query::class)) {
                throw new InvalidArgumentException("Class {$queryClass} must implement Query interface");
            }

            $constructor = $reflection->getConstructor();

            if (!$constructor) {
                throw new InvalidArgumentException("Query {$queryClass} must have a constructor");
            }

            $this->validateRequest($request, $queryClass);

            $parameters = $constructor->getParameters();
            $arguments = $this->buildConstructorArguments($request, $parameters, $queryClass);

            return $reflection->newInstanceArgs($arguments);

        } catch (ReflectionException $e) {
            throw new InvalidArgumentException("Failed to create query {$queryClass}: " . $e->getMessage());
        }
    }

    public function fromRequestWithPagination(Request $request, string $queryClass): Query
    {
        $paginationDefaults = [
            'page' => 1,
            'per_page' => 15,
            'sort' => 'id',
            'order' => 'asc'
        ];

        foreach ($paginationDefaults as $key => $default) {
            if (!$request->has($key)) {
                $request->merge([$key => $default]);
            }
        }

        return $this->fromRequest($request, $queryClass);
    }

    private function validateRequest(Request $request, string $queryClass): void
    {
        if (!isset($this->validationRules[$queryClass])) {
            return;
        }

        $rules = $this->validationRules[$queryClass];
        $validator = validator($request->all(), $rules);

        if ($validator->fails()) {
            throw new InvalidArgumentException('Request validation failed: ' . implode(', ', $validator->errors()->all()));
        }
    }
}
