<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeQueryCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:query {context : The bounded context name} {name : The query name} {--projector : Also create event projector} {--model= : Specify read model name}';
    protected $description = 'Create a new CQRS query handler with read model';

    public function handle(): int
    {
        $contextName = $this->argument('context');
        $queryName = $this->argument('name');
        $createProjector = $this->option('projector');
        $customModelName = $this->option('model');

        // Validate context name
        if (!$this->validateContextName($contextName)) {
            $this->error('Context name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Validate query name
        if (!$this->validateQueryName($queryName)) {
            $this->error('Query name must start with uppercase letter and contain only letters and numbers.');
            $this->line('Query names should be descriptive (e.g., GetOrderHistory, FindActiveCustomers, SearchProducts)');
            return 1;
        }

        // Check if context exists
        if (!$this->contextExists($contextName)) {
            $this->error("Context '{$contextName}' does not exist. Create it first with: php artisan ddd:context {$contextName}");
            return 1;
        }

        // Check if query already exists
        if ($this->queryExists($contextName, $queryName)) {
            $this->error("Query '{$queryName}' already exists in context '{$contextName}'!");
            return 1;
        }

        // Determine read model name
        $readModelName = $customModelName ?: $this->generateReadModelName($queryName);

        // Validate query naming convention
        if (!$this->isQueryName($queryName)) {
            $this->warn("Query name '{$queryName}' should be descriptive (e.g., GetOrderHistory, FindActiveCustomers)");
            if (!$this->confirm('Continue anyway?')) {
                return 1;
            }
        }

        $this->info("Creating CQRS query: {$queryName} in context: {$contextName}");

        // Create query files
        $this->createQueryHandlerFile($contextName, $queryName, $readModelName);
        $this->createReadModelFile($contextName, $readModelName);

        // Create projector if requested
        if ($createProjector) {
            $this->createProjectorFile($contextName, $queryName, $readModelName);
        }

        $this->info("✅ CQRS query '{$queryName}' created successfully in '{$contextName}' context!");
        $this->line('');
        $this->line('Files created:');
        $this->line("📁 modules/{$contextName}/Application/Queries/{$queryName}QueryHandler.php");
        $this->line("📁 modules/{$contextName}/Application/DTOs/{$readModelName}.php");
        
        if ($createProjector) {
            $this->line("📁 modules/{$contextName}/Application/Projectors/{$readModelName}Projector.php");
        }
        
        $this->line('');
        $this->line('Usage example:');
        $this->line("// Create query handler instance");
        $this->line("\$queryHandler = new {$queryName}QueryHandler();");
        $this->line("");
        $this->line("// Execute query");
        $this->line("\$result = \$queryHandler->handle(/* query parameters */);");
        $this->line('');
        $this->line('Next steps:');
        $this->line("• Implement query logic in {$queryName}QueryHandler");
        $this->line("• Define read model structure in {$readModelName}");
        if ($createProjector) {
            $this->line("• Configure event listeners in {$readModelName}Projector");
        } else {
            $this->line("• php artisan ddd:query {$contextName} {$queryName} --projector");
        }
        $this->line("• Handlers are auto-discovered and registered with the Query Bus");

        return 0;
    }

    /**
     * Validate query name.
     */
    private function validateQueryName(string $queryName): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $queryName);
    }

    /**
     * Check if query exists.
     */
    private function queryExists(string $context, string $queryName): bool
    {
        $queryPath = $this->getContextPath($context) . "/Application/Queries/{$queryName}QueryHandler.php";
        return $this->files->exists($queryPath);
    }

    /**
     * Check if query name follows convention.
     */
    private function isQueryName(string $queryName): bool
    {
        $queryIndicators = [
            'Get', 'Find', 'Search', 'List', 'Fetch', 'Load', 
            'Retrieve', 'Query', 'View', 'Show', 'Count', 'Sum'
        ];

        foreach ($queryIndicators as $indicator) {
            if (str_starts_with($queryName, $indicator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate read model name from query name.
     */
    private function generateReadModelName(string $queryName): string
    {
        // Remove query prefixes and add appropriate suffix
        $prefixes = ['Get', 'Find', 'Search', 'List', 'Fetch', 'Load', 'Retrieve', 'Query'];
        
        foreach ($prefixes as $prefix) {
            if (str_starts_with($queryName, $prefix)) {
                $modelName = substr($queryName, strlen($prefix));
                return $modelName . (str_ends_with($modelName, 'Item') ? '' : 'Item');
            }
        }

        return $queryName . 'ReadModel';
    }

    /**
     * Create the query handler file.
     */
    private function createQueryHandlerFile(string $contextName, string $queryName, string $readModelName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $queryName),
            [
                '{{ queryName }}' => $queryName,
                '{{ queryHandler }}' => $queryName . 'QueryHandler',
                '{{ readModel }}' => $readModelName,
                '{{ readModelVariable }}' => $this->getVariableName($readModelName),
                '{{ queryVariable }}' => $this->getVariableName($queryName),
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('query-handler'),
            $this->getContextPath($contextName) . "/Application/Queries/{$queryName}QueryHandler.php",
            $replacements
        );
    }

    /**
     * Create the read model file.
     */
    private function createReadModelFile(string $contextName, string $readModelName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $readModelName),
            [
                '{{ readModel }}' => $readModelName,
                '{{ readModelVariable }}' => $this->getVariableName($readModelName),
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('read-model'),
            $this->getContextPath($contextName) . "/Application/DTOs/{$readModelName}.php",
            $replacements
        );
    }

    /**
     * Create the projector file.
     */
    private function createProjectorFile(string $contextName, string $queryName, string $readModelName): void
    {
        $projectorName = $readModelName . 'Projector';
        
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $projectorName),
            [
                '{{ projectorName }}' => $projectorName,
                '{{ readModel }}' => $readModelName,
                '{{ readModelVariable }}' => $this->getVariableName($readModelName),
                '{{ queryName }}' => $queryName,
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('projector'),
            $this->getContextPath($contextName) . "/Application/Projectors/{$projectorName}.php",
            $replacements
        );
    }

    /**
     * Get variable name from class name.
     */
    private function getVariableName(string $className): string
    {
        return Str::camel($className);
    }
}