<?php

namespace Modules\Users\Domain\Events;

use DateTimeImmutable;
use Modules\Shared\Domain\Events\DomainEvent;

final readonly class UserWasUpdated extends DomainEvent
{
    public function __construct(
        public string $userId,
        public array  $changes,
        public DateTimeImmutable $updatedAt
    ) {
        parent::__construct($userId);
    }

    public function eventName(): string
    {
        return 'user.was_updated';
    }

    /**
     * Serialización con los cambios específicos del evento.
     */
    public function array(): array
    {
        return array_merge(parent::array(), [
            'user_id' => $this->aggregateId,
            'changes' => $this->changes,
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Obtener solo los campos que cambiaron.
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * Verificar si un campo específico cambió.
     */
    public function hasChangedField(string $field): bool
    {
        return array_key_exists($field, $this->changes);
    }

    /**
     * Obtener el valor nuevo de un campo específico.
     */
    public function getNewValue(string $field): mixed
    {
        return $this->changes[$field]['new'] ?? null;
    }

    /**
     * Obtener el valor anterior de un campo específico.
     */
    public function getOldValue(string $field): mixed
    {
        return $this->changes[$field]['old'] ?? null;
    }

    /**
     * Obtener solo los campos que cambiaron (sin metadata).
     */
    public function getChangedFields(): array
    {
        return array_keys($this->changes);
    }

    /**
     * Factory method para crear evento desde cambios simples.
     *
     * Ejemplo de uso:
     * UserWasUpdated::fromSimpleChanges($userId, [
     *     'name' => ['old' => 'John', 'new' => 'John Doe'],
     *     'email' => ['old' => 'john@old.com', 'new' => 'john@new.com']
     * ]);
     */
    public static function fromSimpleChanges(string $userId, array $changes): self
    {
        return new self($userId, $changes, new DateTimeImmutable());
    }

    /**
     * Payload específico para event handlers que solo necesitan los nuevos valores.
     */
    public function newValuesPayload(): array
    {
        $newValues = [];
        foreach ($this->changes as $field => $change) {
            $newValues[$field] = $change['new'] ?? null;
        }

        return [
            'user_id' => $this->aggregateId,
            'updated_fields' => $newValues,
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
