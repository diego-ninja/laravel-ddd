<?php

namespace Modules\Shared\Infrastructure\Transformers;

use Modules\Shared\Domain\Contracts\ValueObject;
use Modules\Shared\Domain\ValueObjects\AbstractValueObject;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use InvalidArgumentException;

/**
 * Base transformer abstracto con funcionalidad común de reflexión
 * para crear objetos automáticamente analizando constructores.
 */
abstract class BaseTransformer
{
    protected array $customMappings = [];
    protected array $validationRules = [];

    /**
     * Configurar mapeos customizados para casos especiales.
     */
    public function setCustomMappings(array $mappings): void
    {
        $this->customMappings = $mappings;
    }

    /**
     * Configurar reglas de validación.
     */
    public function setValidationRules(array $rules): void
    {
        $this->validationRules = $rules;
    }

    /**
     * Construir argumentos del constructor analizando parámetros.
     */
    protected function buildConstructorArguments(mixed $source, array $parameters, string $targetClass): array
    {
        $arguments = [];
        $customMapping = $this->customMappings[$targetClass] ?? [];

        foreach ($parameters as $parameter) {
            $paramName = $parameter->getName();
            
            // Usar mapeo customizado si existe
            if (isset($customMapping[$paramName])) {
                $arguments[] = $this->applyCustomMapping($source, $customMapping[$paramName]);
                continue;
            }

            $value = $this->resolveParameterValue($source, $parameter);
            $arguments[] = $value;
        }

        return $arguments;
    }

    /**
     * Resolver valor del parámetro basado en su tipo y nombre.
     */
    protected function resolveParameterValue(mixed $source, ReflectionParameter $parameter): mixed
    {
        $paramName = $parameter->getName();
        $paramType = $parameter->getType();
        
        // Si es nullable y no existe en source, retornar null o default
        if ($parameter->allowsNull() && !$this->hasValue($source, $paramName)) {
            return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
        }

        // Si no hay tipo definido, usar valor directo
        if (!$paramType) {
            return $this->getValue($source, $paramName);
        }

        $typeName = $paramType->getName();

        // Casos especiales por tipo
        return match (true) {
            // Value Objects
            $this->isValueObjectType($typeName) => $this->createValueObjectFromSource($typeName, $source, $paramName),
            
            // Tipos primitivos
            $typeName === 'string' => $this->getStringValue($source, $paramName, $parameter),
            $typeName === 'int' => $this->getIntValue($source, $paramName, $parameter),
            $typeName === 'float' => $this->getFloatValue($source, $paramName, $parameter),
            $typeName === 'bool' => $this->getBoolValue($source, $paramName, $parameter),
            $typeName === 'array' => $this->getArrayValue($source, $paramName, $parameter),
            
            // DateTimeImmutable
            $typeName === 'DateTimeImmutable' => $this->createDateTimeImmutableFromSource($source, $paramName),
            
            // Default: intentar obtener valor directo
            default => $this->getValue($source, $paramName)
        };
    }

    /**
     * Verificar si un tipo es Value Object.
     */
    protected function isValueObjectType(string $typeName): bool
    {
        if (!class_exists($typeName)) {
            return false;
        }

        try {
            $reflection = new ReflectionClass($typeName);
            return $reflection->implementsInterface(ValueObject::class) || 
                   $reflection->isSubclassOf(AbstractValueObject::class);
        } catch (ReflectionException) {
            return false;
        }
    }

    /**
     * Crear Value Object desde source.
     */
    protected function createValueObjectFromSource(string $valueObjectClass, mixed $source, string $paramName): ValueObject
    {
        $value = $this->getValue($source, $paramName);
        
        try {
            $reflection = new ReflectionClass($valueObjectClass);
            
            // Intentar factory methods comunes
            if ($reflection->hasMethod('fromString') && is_string($value)) {
                return $valueObjectClass::fromString($value);
            }
            
            if ($reflection->hasMethod('fromValue')) {
                return $valueObjectClass::fromValue($value);
            }
            
            if ($reflection->hasMethod('fromUuid') && is_string($value)) {
                return $valueObjectClass::fromUuid($value);
            }
            
            // Constructor directo
            return new $valueObjectClass($value);
            
        } catch (ReflectionException $e) {
            throw new InvalidArgumentException("Failed to create Value Object {$valueObjectClass} from field '{$paramName}': " . $e->getMessage());
        }
    }

    /**
     * Obtener valor string del source.
     */
    protected function getStringValue(mixed $source, string $paramName, ReflectionParameter $parameter): ?string
    {
        $value = $this->getValue($source, $paramName);
        
        if ($value === null) {
            return $parameter->allowsNull() ? null : 
                   ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : '');
        }
        
        return (string) $value;
    }

    /**
     * Obtener valor int del source.
     */
    protected function getIntValue(mixed $source, string $paramName, ReflectionParameter $parameter): ?int
    {
        $value = $this->getValue($source, $paramName);
        
        if ($value === null) {
            return $parameter->allowsNull() ? null : 
                   ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : 0);
        }
        
        return (int) $value;
    }

    /**
     * Obtener valor float del source.
     */
    protected function getFloatValue(mixed $source, string $paramName, ReflectionParameter $parameter): ?float
    {
        $value = $this->getValue($source, $paramName);
        
        if ($value === null) {
            return $parameter->allowsNull() ? null : 
                   ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : 0.0);
        }
        
        return (float) $value;
    }

    /**
     * Obtener valor bool del source.
     */
    protected function getBoolValue(mixed $source, string $paramName, ReflectionParameter $parameter): ?bool
    {
        $value = $this->getValue($source, $paramName);
        
        if ($value === null) {
            return $parameter->allowsNull() ? null : 
                   ($parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : false);
        }
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Obtener valor array del source.
     */
    protected function getArrayValue(mixed $source, string $paramName, ReflectionParameter $parameter): array
    {
        $value = $this->getValue($source, $paramName);
        
        if ($value === null) {
            return $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : [];
        }
        
        if (is_array($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [$value];
        }
        
        return [$value];
    }

    /**
     * Crear DateTimeImmutable desde field del source.
     */
    protected function createDateTimeImmutableFromSource(mixed $source, string $paramName): ?\DateTimeImmutable
    {
        $value = $this->getValue($source, $paramName);
        
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        if ($value instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($value);
        }

        if (is_string($value)) {
            return new \DateTimeImmutable($value);
        }

        return null;
    }

    /**
     * Aplicar mapeo customizado.
     */
    protected function applyCustomMapping(mixed $source, callable|string $mapping): mixed
    {
        if (is_callable($mapping)) {
            return $mapping($source);
        }
        
        return $this->getValue($source, $mapping);
    }

    /**
     * Convertir camelCase a snake_case.
     */
    protected function camelToSnake(string $input): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Convertir a snake_case.
     */
    protected function toSnakeCase(string $input): string
    {
        return strtolower(str_replace(['-', ' '], '_', $input));
    }

    /**
     * Convertir valor para persistencia.
     */
    protected function convertValueForPersistence(mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof ValueObject || $value instanceof AbstractValueObject) {
            return $value->value();
        }

        if ($value instanceof \DateTimeImmutable || $value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_array($value)) {
            return array_map([$this, 'convertValueForPersistence'], $value);
        }

        return $value;
    }

    /**
     * Obtener nombre del campo para persistencia.
     */
    protected function getFieldName(string $propertyName): string
    {
        // Convertir camelCase a snake_case para compatibilidad con DB
        return $this->camelToSnake($propertyName);
    }

    // Abstract methods que deben implementar las clases hijas

    /**
     * Verificar si existe un valor en el source.
     */
    abstract protected function hasValue(mixed $source, string $key): bool;

    /**
     * Obtener valor del source.
     */
    abstract protected function getValue(mixed $source, string $key): mixed;

    /**
     * Factory method para crear transformer con configuración.
     */
    public static function create(array $customMappings = [], array $validationRules = []): static
    {
        $transformer = new static();
        $transformer->setCustomMappings($customMappings);
        $transformer->setValidationRules($validationRules);
        return $transformer;
    }
}