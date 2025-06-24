<?php

namespace Modules\Shared\Application\Traits;

use Illuminate\Http\Request;
use Modules\Shared\Infrastructure\Transformers\CommandTransformer;

trait CreatesFromRequest
{
    public static function fromRequest(Request $request): static
    {
        return self::createTransformer()->fromRequest($request, static::class);
    }

    private static function createTransformer(): CommandTransformer
    {
        return CommandTransformer::create(
            static::getCustomMappings(),
            static::getValidationRules()
        );
    }

    /**
     * @return array ['property' => 'request_field' | callable]
     */
    protected static function getCustomMappings(): array
    {
        return [];
    }

    /**
     * @return array ['field' => 'validation_rules']
     */
    protected static function getValidationRules(): array
    {
        return [];
    }

    public static function fromArray(array $data): static
    {
        $request = new Request($data);
        return self::fromRequest($request);
    }

    public static function fromJson(string $json): static
    {
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON provided: ' . json_last_error_msg());
        }

        return self::fromArray($data);
    }
}
