<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeEndpointCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:endpoint {context : The bounded context name} {name : The endpoint name}';
    protected $description = 'Create a new DDD invokable controller (single-action endpoint)';

    public function handle(): int
    {
        $contextName = $this->argument('context');
        $endpointName = $this->argument('name');

        // Validate context name
        if (!$this->validateContextName($contextName)) {
            $this->error('Context name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Check if context exists
        if (!$this->contextExists($contextName)) {
            $this->error("Context '{$contextName}' does not exist! Create it first with: php artisan ddd:context {$contextName}");
            return 1;
        }

        // Validate and clean endpoint name
        $endpointName = $this->cleanEndpointName($endpointName);

        if (!$this->validateEndpointName($endpointName)) {
            $this->error('Endpoint name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        $filePath = $this->getContextPath($contextName) . "/Infrastructure/Http/Controllers/{$endpointName}Endpoint.php";

        // Check if endpoint already exists
        if ($this->files->exists($filePath)) {
            $this->error("Endpoint '{$endpointName}Endpoint' already exists in context '{$contextName}'!");
            return 1;
        }

        $this->info("Creating invokable endpoint: {$endpointName}Endpoint in {$contextName} context");

        // Create the endpoint
        $this->createEndpoint($contextName, $endpointName, $filePath);

        $this->info("✅ Invokable endpoint '{$endpointName}Endpoint' created successfully!");
        $this->line('');
        $this->line("📁 Created: modules/{$contextName}/Infrastructure/Http/Controllers/{$endpointName}Endpoint.php");
        $this->line('');
        $this->line('Usage in routes:');
        $this->line("Route::post('/your-route', \\Modules\\{$contextName}\\Infrastructure\\Http\\Controllers\\{$endpointName}Endpoint::class);");
        $this->line('');
        $this->line('Next steps:');
        $this->line("• Add route in your routes file");
        $this->line("• Create corresponding command or query:");
        
        // Suggest appropriate command or query based on name pattern
        if ($this->isCommandPattern($endpointName)) {
            $this->line("  php artisan ddd:command {$contextName} {$endpointName}");
        } elseif ($this->isQueryPattern($endpointName)) {
            $this->line("  php artisan ddd:query {$contextName} {$endpointName}");
        } else {
            $this->line("  php artisan ddd:command {$contextName} {$endpointName} (for write operations)");
            $this->line("  php artisan ddd:query {$contextName} {$endpointName} (for read operations)");
        }

        return 0;
    }

    /**
     * Create the endpoint file.
     */
    private function createEndpoint(string $contextName, string $endpointName, string $filePath): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName),
            [
                '{{ name }}' => $endpointName,
                '{{ nameLower }}' => Str::camel($endpointName),
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('endpoint'),
            $filePath,
            $replacements
        );
    }

    /**
     * Clean the endpoint name by removing "Endpoint" suffix if present.
     */
    private function cleanEndpointName(string $name): string
    {
        return Str::studly(str_replace('Endpoint', '', $name));
    }

    /**
     * Validate endpoint name format.
     */
    private function validateEndpointName(string $name): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name);
    }

    /**
     * Check if the endpoint name suggests a command pattern.
     */
    private function isCommandPattern(string $name): bool
    {
        $commandPrefixes = ['Create', 'Update', 'Delete', 'Place', 'Cancel', 'Process', 'Execute', 'Send', 'Generate'];
        
        foreach ($commandPrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Check if the endpoint name suggests a query pattern.
     */
    private function isQueryPattern(string $name): bool
    {
        $queryPrefixes = ['Get', 'Find', 'Search', 'List', 'Show', 'Retrieve', 'Fetch'];
        
        foreach ($queryPrefixes as $prefix) {
            if (str_starts_with($name, $prefix)) {
                return true;
            }
        }
        
        return false;
    }
}