<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeEntityCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:entity {context : The bounded context name} {name : The entity name}';
    protected $description = 'Create a new DDD entity with aggregate root functionality';

    public function handle(): int
    {
        $contextName = $this->argument('context');
        $entityName = $this->argument('name');

        // Validate context name
        if (!$this->validateContextName($contextName)) {
            $this->error('Context name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Validate entity name
        if (!$this->validateEntityName($entityName)) {
            $this->error('Entity name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Check if context exists
        if (!$this->contextExists($contextName)) {
            $this->error("Context '{$contextName}' does not exist. Create it first with: php artisan ddd:context {$contextName}");
            return 1;
        }

        // Check if entity already exists
        if ($this->entityExists($contextName, $entityName)) {
            $this->error("Entity '{$entityName}' already exists in context '{$contextName}'!");
            return 1;
        }

        $this->info("Creating entity: {$entityName} in context: {$contextName}");

        // Create entity file
        $this->createEntityFile($contextName, $entityName);

        // Create entity ID value object
        $this->createEntityIdValueObject($contextName, $entityName);

        $this->info("✅ Entity '{$entityName}' created successfully in '{$contextName}' context!");
        $this->line('');
        $this->line('Files created:');
        $this->line("📁 modules/{$contextName}/Domain/Entities/{$entityName}.php");
        $this->line("📁 modules/{$contextName}/Domain/ValueObjects/{$entityName}Id.php");
        $this->line('');
        $this->line('Next steps:');
        $this->line("• php artisan ddd:repository {$contextName} {$entityName}");
        $this->line("• php artisan ddd:event {$contextName} {$entityName}WasCreated");
        $this->line("• php artisan ddd:value-object {$contextName} <ValueObjectName>");

        return 0;
    }

    /**
     * Validate entity name.
     */
    private function validateEntityName(string $entityName): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $entityName);
    }

    /**
     * Check if entity exists.
     */
    private function entityExists(string $context, string $entityName): bool
    {
        $entityPath = $this->getContextPath($context) . "/Domain/Entities/{$entityName}.php";
        return $this->files->exists($entityPath);
    }

    /**
     * Create the entity file.
     */
    private function createEntityFile(string $contextName, string $entityName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $entityName),
            [
                '{{ entityId }}' => $entityName . 'Id',
                '{{ entityIdVariable }}' => Str::camel($entityName) . 'Id',
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('entity'),
            $this->getContextPath($contextName) . "/Domain/Entities/{$entityName}.php",
            $replacements
        );
    }

    /**
     * Create the entity ID value object.
     */
    private function createEntityIdValueObject(string $contextName, string $entityName): void
    {
        $idName = $entityName . 'Id';
        
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $idName),
            [
                '{{ entityName }}' => $entityName,
                '{{ entityNameLower }}' => Str::lower($entityName),
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('entity-id'),
            $this->getContextPath($contextName) . "/Domain/ValueObjects/{$idName}.php",
            $replacements
        );
    }
}