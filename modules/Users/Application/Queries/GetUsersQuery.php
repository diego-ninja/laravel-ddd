<?php

namespace Modules\Users\Application\Queries;

use Modules\Shared\Application\Contracts\Query;
use Modules\Shared\Application\Traits\HasGenericQueryFromRequest;

/**
 * Query para obtener listado de usuarios con paginación y filtros.
 * Usa el transformer genérico para mapear automáticamente desde Request.
 */
final readonly class GetUsersQuery implements Query
{
    use HasGenericQueryFromRequest;

    /**
     * Query inmutable con propiedades públicas readonly.
     * fromRequest genérico analiza automáticamente el constructor y mapea desde Request.
     */
    public function __construct(
        public int     $page = 1,
        public int     $perPage = 15,
        public string  $sort = 'id',
        public string  $order = 'asc',
        public ?string $search = null,
        public ?string $email = null,
        public ?bool   $active = null,
        public ?string $createdAfter = null,
        public ?string $createdBefore = null,
    ) {}

    /**
     * Reglas de validación específicas para GetUsersQuery.
     */
    protected static function getValidationRules(): array
    {
        return array_merge(self::getPaginationValidationRules(), [
            'search' => 'nullable|string|min:2|max:255',
            'email' => 'nullable|email|max:255',
            'active' => 'nullable|boolean',
            'created_after' => 'nullable|date',
            'created_before' => 'nullable|date|after_or_equal:created_after',
        ]);
    }

    /**
     * Mapeos customizados para casos especiales.
     */
    protected static function getCustomMappings(): array
    {
        return [
            'perPage' => 'per_page', // Mapear per_page del request a perPage
            'createdAfter' => 'created_after',
            'createdBefore' => 'created_before',
        ];
    }

    /**
     * Convertir a array para serialización si es necesario.
     */
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'per_page' => $this->perPage,
            'sort' => $this->sort,
            'order' => $this->order,
            'search' => $this->search,
            'email' => $this->email,
            'active' => $this->active,
            'created_after' => $this->createdAfter,
            'created_before' => $this->createdBefore,
        ];
    }

    /**
     * Verificar si hay filtros activos.
     */
    public function hasFilters(): bool
    {
        return $this->search !== null ||
               $this->email !== null ||
               $this->active !== null ||
               $this->createdAfter !== null ||
               $this->createdBefore !== null;
    }

    /**
     * Obtener offset para paginación.
     */
    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    /**
     * Verificar si el orden es válido.
     */
    public function isValidOrder(): bool
    {
        return in_array(strtolower($this->order), ['asc', 'desc']);
    }

    /**
     * Obtener campos ordenables permitidos.
     */
    public function getAllowedSortFields(): array
    {
        return ['id', 'email', 'name', 'created_at', 'updated_at'];
    }

    /**
     * Verificar si el campo de ordenamiento es válido.
     */
    public function isValidSortField(): bool
    {
        return in_array($this->sort, $this->getAllowedSortFields());
    }
}