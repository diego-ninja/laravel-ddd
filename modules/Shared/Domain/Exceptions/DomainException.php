<?php

namespace Modules\Shared\Domain\Exceptions;

use DomainException as BaseDomainException;

abstract class DomainException extends BaseDomainException
{
    /**
     * Create exception for invalid data.
     */
    public static function invalidData(string $message): static
    {
        return new static("Invalid data: {$message}");
    }
    
    /**
     * Create exception for entity not found.
     */
    public static function notFound(string $entity, string $identifier): static
    {
        return new static("{$entity} with identifier '{$identifier}' not found");
    }
    
    /**
     * Create exception for business rule violation.
     */
    public static function businessRuleViolation(string $rule): static
    {
        return new static("Business rule violation: {$rule}");
    }

    /**
     * Create exception for unauthorized access.
     */
    public static function unauthorized(string $action = 'perform this action'): static
    {
        return new static("Unauthorized to {$action}");
    }

    /**
     * Create exception for duplicate entity.
     */
    public static function duplicate(string $entity, string $identifier): static
    {
        return new static("{$entity} with identifier '{$identifier}' already exists");
    }

    /**
     * Create exception for invalid state.
     */
    public static function invalidState(string $currentState, string $expectedState): static
    {
        return new static("Invalid state transition from '{$currentState}' to '{$expectedState}'");
    }
}