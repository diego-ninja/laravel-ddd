<?php

namespace Modules\Shared\Infrastructure\Transformers;

use Illuminate\Http\Request;
use Modules\Shared\Application\Contracts\Command;
use Modules\Shared\Domain\Contracts\ValueObject;
use Modules\Shared\Domain\ValueObjects\AbstractValueObject;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use InvalidArgumentException;

/**
 * Transformer genérico para crear Commands desde Laravel Request
 * usando reflexión para análisis automático de propiedades del constructor.
 */
final class CommandTransformer extends BaseTransformer
{
    /**
     * Verificar si existe un valor en el Request.
     */
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

    /**
     * Obtener valor del Request con múltiples variaciones de nombre.
     */
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

    /**
     * Crear Command desde Request usando reflexión automática.
     */
    public function fromRequest(Request $request, string $commandClass): Command
    {
        try {
            $reflection = new ReflectionClass($commandClass);
            
            if (!$reflection->implementsInterface(Command::class)) {
                throw new InvalidArgumentException("Class {$commandClass} must implement Command interface");
            }

            $constructor = $reflection->getConstructor();
            
            if (!$constructor) {
                throw new InvalidArgumentException("Command {$commandClass} must have a constructor");
            }

            // Validar request si hay reglas configuradas
            $this->validateRequest($request, $commandClass);

            $parameters = $constructor->getParameters();
            $arguments = $this->buildConstructorArguments($request, $parameters, $commandClass);

            return $reflection->newInstanceArgs($arguments);
            
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException("Failed to create command {$commandClass}: " . $e->getMessage());
        }
    }

    /**
     * Validar request si hay reglas configuradas.
     */
    private function validateRequest(Request $request, string $commandClass): void
    {
        if (!isset($this->validationRules[$commandClass])) {
            return;
        }

        $rules = $this->validationRules[$commandClass];
        $validator = validator($request->all(), $rules);
        
        if ($validator->fails()) {
            throw new InvalidArgumentException('Request validation failed: ' . implode(', ', $validator->errors()->all()));
        }
    }
}