<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeValueObjectCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:value-object {context : The bounded context name} {name : The value object name} {--type= : The type of value object (string, email, money, date, enum)}';
    protected $description = 'Create a new DDD value object with appropriate validation';

    private const AVAILABLE_TYPES = ['string', 'email', 'money', 'date', 'enum', 'number'];

    public function handle(): int
    {
        $contextName = $this->argument('context');
        $valueObjectName = $this->argument('name');
        $type = $this->option('type');

        // Validate context name
        if (!$this->validateContextName($contextName)) {
            $this->error('Context name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Validate value object name
        if (!$this->validateValueObjectName($valueObjectName)) {
            $this->error('Value object name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Check if context exists
        if (!$this->contextExists($contextName)) {
            $this->error("Context '{$contextName}' does not exist. Create it first with: php artisan ddd:context {$contextName}");
            return 1;
        }

        // Check if value object already exists
        if ($this->valueObjectExists($contextName, $valueObjectName)) {
            $this->error("Value object '{$valueObjectName}' already exists in context '{$contextName}'!");
            return 1;
        }

        // Determine type if not provided
        if (!$type) {
            $type = $this->detectType($valueObjectName);
        }

        // Validate type
        if (!in_array($type, self::AVAILABLE_TYPES)) {
            $this->error("Invalid type '{$type}'. Available types: " . implode(', ', self::AVAILABLE_TYPES));
            return 1;
        }

        $this->info("Creating value object: {$valueObjectName} (type: {$type}) in context: {$contextName}");

        // Create value object file
        $this->createValueObjectFile($contextName, $valueObjectName, $type);

        $this->info("✅ Value object '{$valueObjectName}' created successfully in '{$contextName}' context!");
        $this->line('');
        $this->line('File created:');
        $this->line("📁 modules/{$contextName}/Domain/ValueObjects/{$valueObjectName}.php");
        $this->line('');
        $this->line("Usage example:");
        $this->showUsageExample($valueObjectName, $type);

        return 0;
    }

    /**
     * Validate value object name.
     */
    private function validateValueObjectName(string $name): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name);
    }

    /**
     * Check if value object exists.
     */
    private function valueObjectExists(string $context, string $name): bool
    {
        $path = $this->getContextPath($context) . "/Domain/ValueObjects/{$name}.php";
        return $this->files->exists($path);
    }

    /**
     * Detect type based on value object name.
     */
    private function detectType(string $name): string
    {
        $lowerName = strtolower($name);
        
        if (str_contains($lowerName, 'email')) {
            return 'email';
        }
        
        if (str_contains($lowerName, 'price') || str_contains($lowerName, 'money') || str_contains($lowerName, 'amount')) {
            return 'money';
        }
        
        if (str_contains($lowerName, 'date') || str_contains($lowerName, 'time')) {
            return 'date';
        }
        
        if (str_contains($lowerName, 'status') || str_contains($lowerName, 'type') || str_contains($lowerName, 'state')) {
            return 'enum';
        }
        
        if (str_contains($lowerName, 'count') || str_contains($lowerName, 'number') || str_contains($lowerName, 'quantity')) {
            return 'number';
        }
        
        return 'string';
    }

    /**
     * Create the value object file.
     */
    private function createValueObjectFile(string $contextName, string $valueObjectName, string $type): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $valueObjectName),
            [
                '{{ type }}' => $type,
                '{{ typeCamel }}' => Str::camel($type),
            ]
        );

        $stubName = "value-object-{$type}";
        
        $this->generateFromStub(
            $this->getStubPath($stubName),
            $this->getContextPath($contextName) . "/Domain/ValueObjects/{$valueObjectName}.php",
            $replacements
        );
    }

    /**
     * Show usage example for the created value object.
     */
    private function showUsageExample(string $name, string $type): void
    {
        $examples = [
            'string' => "\${$this->getVariableName($name)} = {$name}::fromString('example value');",
            'email' => "\${$this->getVariableName($name)} = {$name}::fromString('user@example.com');",
            'money' => "\${$this->getVariableName($name)} = {$name}::fromFloat(99.99, 'USD');",
            'date' => "\${$this->getVariableName($name)} = {$name}::fromString('2023-12-25');",
            'enum' => "\${$this->getVariableName($name)} = {$name}::ACTIVE();",
            'number' => "\${$this->getVariableName($name)} = {$name}::fromInt(42);",
        ];

        $this->line($examples[$type] ?? "\${$this->getVariableName($name)} = {$name}::fromString('value');");
    }

    /**
     * Get variable name from class name.
     */
    private function getVariableName(string $className): string
    {
        return Str::camel($className);
    }
}