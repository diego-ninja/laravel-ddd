<?php

namespace Modules\Shared\Infrastructure\Exceptions;

use Exception;

abstract class InfrastructureException extends Exception
{
    /**
     * Create exception for persistence failure.
     */
    public static function persistenceFailed(string $entity, string $message = ''): static
    {
        $fullMessage = "Failed to persist {$entity}";
        if ($message) {
            $fullMessage .= ": {$message}";
        }
        
        return new static($fullMessage);
    }

    /**
     * Create exception for external service failure.
     */
    public static function externalServiceFailed(string $service, string $message = ''): static
    {
        $fullMessage = "External service '{$service}' failed";
        if ($message) {
            $fullMessage .= ": {$message}";
        }
        
        return new static($fullMessage);
    }

    /**
     * Create exception for configuration error.
     */
    public static function configurationError(string $component, string $message): static
    {
        return new static("Configuration error in '{$component}': {$message}");
    }

    /**
     * Create exception for connection failure.
     */
    public static function connectionFailed(string $service, string $message = ''): static
    {
        $fullMessage = "Failed to connect to '{$service}'";
        if ($message) {
            $fullMessage .= ": {$message}";
        }
        
        return new static($fullMessage);
    }
}