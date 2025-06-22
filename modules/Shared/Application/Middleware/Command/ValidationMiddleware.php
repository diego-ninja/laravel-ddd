<?php

namespace Modules\Shared\Application\Middleware\Command;

use Modules\Shared\Application\Contracts\Middleware;
use Illuminate\Validation\Factory as ValidatorFactory;
use Illuminate\Validation\ValidationException;
use Closure;
final readonly class ValidationMiddleware implements Middleware
{
    public function __construct(
        private ValidatorFactory $validatorFactory
    ) {}

    public function handle(object $message, Closure $next): mixed
    {
        $this->validateCommand($message);
        return $next($message);
    }

    private function validateCommand(object $command): void
    {
        $rules = $this->getValidationRules($command);

        if (empty($rules)) {
            return;
        }

        $data = $this->extractCommandData($command);

        $validator = $this->validatorFactory->make($data, $rules);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    private function getValidationRules(object $command): array
    {
        if (method_exists($command, 'rules')) {
            return $command->rules();
        }

        return [];
    }

    private function extractCommandData(object $command): array
    {
        $reflection = new \ReflectionClass($command);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPublic()) {
                $data[$property->getName()] = $property->getValue($command);
            }
        }

        return $data;
    }
}
