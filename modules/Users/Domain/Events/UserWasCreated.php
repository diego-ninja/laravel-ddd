<?php

namespace Modules\Users\Domain\Events;

use DateTimeImmutable;
use Modules\Shared\Domain\Events\DomainEvent;

final readonly class UserWasCreated extends DomainEvent
{
    /**
     * UserWasCreated contiene TODOS los datos del agregado User creado.
     * Esto permite a otros bounded contexts reconstruir el estado completo
     * sin necesidad de hacer consultas adicionales (principio DDD).
     */
    public function __construct(
        string $userId,
        public readonly string $email,
        public readonly string $name,
        public readonly string $hashedPassword,
        public readonly DateTimeImmutable $createdAt
    ) {
        parent::__construct($userId);
    }

    public function eventName(): string
    {
        return 'user.was_created';
    }

    /**
     * Serialización completa del evento con todos los datos del agregado.
     * Incluye datos necesarios para reconstruir el estado en otros contextos.
     */
    public function array(): array
    {
        return array_merge(parent::array(), [
            'user_id' => $this->aggregateId,
            'email' => $this->email,
            'name' => $this->name,
            'hashed_password' => $this->hashedPassword,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Payload específico del dominio User para event handlers.
     */
    public function userPayload(): array
    {
        return [
            'user_id' => $this->aggregateId,
            'email' => $this->email,
            'name' => $this->name,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            // Nota: hashedPassword no se incluye en payload por seguridad
        ];
    }

    /**
     * Obtener ID del usuario creado.
     */
    public function userId(): string
    {
        return $this->aggregateId;
    }
}
