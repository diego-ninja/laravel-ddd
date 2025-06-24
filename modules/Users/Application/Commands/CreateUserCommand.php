<?php

namespace Modules\Users\Application\Commands;

use Modules\Shared\Application\Contracts\Command;
use Modules\Shared\Application\Traits\CreatesFromRequest;

final readonly class CreateUserCommand implements Command
{
    use CreatesFromRequest;

    public function __construct(
        public string  $email,
        public string  $password,
        public ?string $name = null,
    ){}

    protected static function getValidationRules(): array
    {
        return [
            'email' => 'required|email|string|max:255',
            'password' => 'required|string|min:8|max:255',
            'name' => 'nullable|string|min:2|max:255',
        ];
    }

    protected static function getCustomMappings(): array
    {
        return [
            // Ejemplo de mapeo customizado:
            // 'email' => fn(Request $request) => strtolower($request->input('email'))
        ];
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => '***', // No exponer password en serialización
            'name' => $this->name,
        ];
    }
}
