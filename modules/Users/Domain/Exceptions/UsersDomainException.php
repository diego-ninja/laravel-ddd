<?php

namespace Modules\Users\Domain\Exceptions;

use DomainException;

class UsersDomainException extends DomainException
{
    public static function invalidData(string $message): self
    {
        return new self("Invalid data: {$message}");
    }
    
    public static function notFound(string $entity, string $identifier): self
    {
        return new self("{$entity} with identifier '{$identifier}' not found");
    }
    
    public static function businessRuleViolation(string $rule): self
    {
        return new self("Business rule violation: {$rule}");
    }
}