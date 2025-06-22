<?php

namespace Modules\Shared\Application\Exceptions;

use Exception;

abstract class ApplicationException extends Exception
{
    /**
     * Create exception for validation failure.
     */
    public static function validationFailed(string $message): static
    {
        return new static("Validation failed: {$message}");
    }

    /**
     * Create exception for handler not found.
     */
    public static function handlerNotFound(string $handlerType, string $className): static
    {
        return new static("No {$handlerType} handler found for '{$className}'");
    }

    /**
     * Create exception for bus configuration error.
     */
    public static function busConfigurationError(string $message): static
    {
        return new static("Bus configuration error: {$message}");
    }

    /**
     * Create exception for middleware error.
     */
    public static function middlewareError(string $middleware, string $message): static
    {
        return new static("Middleware '{$middleware}' error: {$message}");
    }
}