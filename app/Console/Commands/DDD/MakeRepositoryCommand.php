<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeRepositoryCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:repository {context : The bounded context name} {entity : The entity name} {--model : Also create Eloquent model}';
    protected $description = 'Create a new DDD repository interface and Eloquent implementation';

    public function handle(): int
    {
        $contextName = $this->argument('context');
        $entityName = $this->argument('entity');
        $createModel = $this->option('model');

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

        // Check if entity exists
        if (!$this->entityExists($contextName, $entityName)) {
            $this->error("Entity '{$entityName}' does not exist in context '{$contextName}'. Create it first with: php artisan ddd:entity {$contextName} {$entityName}");
            return 1;
        }

        // Check if repository already exists
        if ($this->repositoryExists($contextName, $entityName)) {
            $this->error("Repository for '{$entityName}' already exists in context '{$contextName}'!");
            return 1;
        }

        $this->info("Creating repository for entity: {$entityName} in context: {$contextName}");

        // Create repository interface
        $this->createRepositoryInterface($contextName, $entityName);

        // Create Eloquent repository implementation
        $this->createEloquentRepository($contextName, $entityName);

        // Create Eloquent model if requested
        if ($createModel) {
            $this->createEloquentModel($contextName, $entityName);
        }

        // Update Service Provider with bindings
        $this->updateServiceProvider($contextName, $entityName);

        $this->info("✅ Repository for '{$entityName}' created successfully in '{$contextName}' context!");
        $this->line('');
        $this->line('Files created:');
        $this->line("📁 modules/{$contextName}/Domain/Repositories/{$entityName}RepositoryInterface.php");
        $this->line("📁 modules/{$contextName}/Infrastructure/Persistence/Eloquent{$entityName}Repository.php");

        if ($createModel) {
            $this->line("📁 modules/{$contextName}/Infrastructure/Persistence/Eloquent/{$entityName}Model.php");
        }

        $this->line("📁 modules/{$contextName}/Infrastructure/Providers/{$contextName}ServiceProvider.php (updated)");
        $this->line('');
        $this->line('Usage example:');
        $this->line("// In your application layer");
        $this->line("public function __construct(");
        $this->line("    private {$entityName}RepositoryInterface \${$this->getVariableName($entityName)}Repository");
        $this->line(") {}");
        $this->line('');
        $this->line('Next steps:');
        $this->line("• php artisan ddd:command {$contextName} Create{$entityName}");
        $this->line("• php artisan ddd:event {$contextName} {$entityName}WasCreated");

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
     * Check if repository exists.
     */
    private function repositoryExists(string $context, string $entityName): bool
    {
        $interfacePath = $this->getContextPath($context) . "/Domain/Repositories/{$entityName}RepositoryInterface.php";
        return $this->files->exists($interfacePath);
    }

    /**
     * Create the repository interface.
     */
    private function createRepositoryInterface(string $contextName, string $entityName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $entityName),
            [
                '{{ entityId }}' => $entityName . 'Id',
                '{{ repositoryInterface }}' => $entityName . 'RepositoryInterface',
                '{{ entityVariable }}' => $this->getVariableName($entityName),
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('repository-interface'),
            $this->getContextPath($contextName) . "/Domain/Repositories/{$entityName}RepositoryInterface.php",
            $replacements
        );
    }

    /**
     * Create the Eloquent repository implementation.
     */
    private function createEloquentRepository(string $contextName, string $entityName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $entityName),
            [
                '{{ entityId }}' => $entityName . 'Id',
                '{{ repositoryInterface }}' => $entityName . 'RepositoryInterface',
                '{{ eloquentRepository }}' => 'Eloquent' . $entityName . 'Repository',
                '{{ eloquentModel }}' => $entityName . 'Model',
                '{{ entityVariable }}' => $this->getVariableName($entityName),
                '{{ modelVariable }}' => $this->getVariableName($entityName) . 'Model',
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('eloquent-repository'),
            $this->getContextPath($contextName) . "/Infrastructure/Persistence/Eloquent{$entityName}Repository.php",
            $replacements
        );
    }

    /**
     * Create the Eloquent model.
     */
    private function createEloquentModel(string $contextName, string $entityName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $entityName),
            [
                '{{ tableName }}' => Str::snake(Str::plural($entityName)),
                '{{ modelName }}' => $entityName . 'Model',
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('eloquent-model'),
            $this->getContextPath($contextName) . "/Infrastructure/Persistence/Eloquent/{$entityName}Model.php",
            $replacements
        );
    }

    /**
     * Update the Service Provider with repository bindings.
     */
    private function updateServiceProvider(string $contextName, string $entityName): void
    {
        $serviceProviderPath = $this->getContextPath($contextName) . "/Infrastructure/Providers/{$contextName}ServiceProvider.php";

        if (!$this->files->exists($serviceProviderPath)) {
            $this->warn("Service Provider not found. Skipping binding registration.");
            return;
        }

        $content = $this->files->get($serviceProviderPath);

        // Check if binding already exists
        $bindingCheck = "{$entityName}RepositoryInterface::class";
        if (str_contains($content, $bindingCheck)) {
            return; // Binding already exists
        }

        // Find the registerRepositories method and add the binding
        $interfaceClass = "\\{$this->getNamespace($contextName)}\\Domain\\Repositories\\{$entityName}RepositoryInterface::class";
        $implementationClass = "\\{$this->getNamespace($contextName)}\\Infrastructure\\Persistence\\Eloquent{$entityName}Repository::class";

        $newBinding = "        \$this->app->bind(\n            {$interfaceClass},\n            {$implementationClass}\n        );";

        // Replace the comment in registerRepositories method
        $pattern = '/(\s+\/\/ Example:.*?\n\s+\/\/.*?\n\s+\/\/.*?\n\s+\/\/.*?\n\s+\/\/ \);)/s';

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, $newBinding . "\n\n$1", $content);
        } else {
            // Fallback: add before the closing brace of registerRepositories
            $pattern = '/(\s+)(\/\/ Register domain services here)/';
            $content = preg_replace($pattern, "$newBinding\n$1$2", $content);
        }

        $this->files->put($serviceProviderPath, $content);
    }

    /**
     * Get namespace for the context.
     */
    private function getNamespace(string $context): string
    {
        return "Modules\\{$context}";
    }

    /**
     * Get variable name from class name.
     */
    private function getVariableName(string $className): string
    {
        return Str::camel($className);
    }
}
