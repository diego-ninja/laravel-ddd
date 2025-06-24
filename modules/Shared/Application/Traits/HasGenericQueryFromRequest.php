<?php

namespace Modules\Shared\Application\Traits;

use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Transformers\QueryTransformer;

/**
 * Trait que proporciona funcionalidad genérica fromRequest a las Queries
 * usando reflexión automática para mapear Request a propiedades de la Query.
 * Incluye soporte especial para paginación y filtros.
 */
trait HasGenericQueryFromRequest
{
    /**
     * Crear Query desde Request usando transformer genérico.
     */
    public static function fromRequest(Request $request): static
    {
        return self::createTransformer()->fromRequest($request, static::class);
    }

    /**
     * Crear Query desde Request con soporte de paginación automática.
     */
    public static function fromRequestWithPagination(Request $request): static
    {
        return self::createTransformer()->fromRequestWithPagination($request, static::class);
    }

    /**
     * Crear transformer con configuración específica de la Query.
     */
    private static function createTransformer(): QueryTransformer
    {
        return QueryTransformer::create(
            static::getCustomMappings(),
            static::getValidationRules()
        );
    }

    /**
     * Configuración de mapeos customizados para casos especiales.
     * Override en Queries específicas si es necesario.
     * 
     * @return array ['property' => 'request_field' | callable]
     */
    protected static function getCustomMappings(): array
    {
        return [];
    }

    /**
     * Reglas de validación para el Request.
     * Override en Queries específicas si es necesario.
     * 
     * @return array ['field' => 'validation_rules']
     */
    protected static function getValidationRules(): array
    {
        return [];
    }

    /**
     * Reglas de validación específicas para paginación.
     * Pueden ser extendidas por Queries específicas.
     * 
     * @return array
     */
    protected static function getPaginationValidationRules(): array
    {
        return [
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100',
            'sort' => 'string|max:100',
            'order' => 'string|in:asc,desc',
        ];
    }

    /**
     * Crear Query desde array de datos (útil para testing).
     */
    public static function fromArray(array $data): static
    {
        $request = new Request($data);
        return self::fromRequest($request);
    }

    /**
     * Crear Query desde JSON string (útil para APIs).
     */
    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON provided: ' . json_last_error_msg());
        }
        
        return self::fromArray($data);
    }

    /**
     * Crear Query con parámetros de paginación por defecto.
     */
    public static function withDefaultPagination(Request $request = null): static
    {
        $request = $request ?: new Request();
        
        // Merge con valores por defecto si no están presentes
        $defaults = [
            'page' => 1,
            'per_page' => 15,
            'sort' => 'id',
            'order' => 'asc'
        ];
        
        foreach ($defaults as $key => $value) {
            if (!$request->has($key)) {
                $request->merge([$key => $value]);
            }
        }
        
        return self::fromRequest($request);
    }

    /**
     * Crear Query con filtros desde query string.
     * Útil para APIs que reciben filtros como parámetros GET.
     */
    public static function fromQueryString(string $queryString): static
    {
        parse_str($queryString, $data);
        return self::fromArray($data);
    }
}