<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeContextCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:context {name : The name of the bounded context}';
    protected $description = 'Create a new DDD bounded context with complete structure';

    public function handle(): int
    {
        $contextName = $this->argument('name');

        // Validate context name
        if (!$this->validateContextName($contextName)) {
            $this->error('Context name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Check if context already exists
        if ($this->contextExists($contextName)) {
            $this->error("Context '{$contextName}' already exists!");
            return 1;
        }

        $this->info("Creating bounded context: {$contextName}");

        // Create directory structure
        $this->createDirectoryStructure($contextName);

        // Create base files
        $this->createBaseFiles($contextName);

        $this->info("✅ Bounded context '{$contextName}' created successfully!");
        $this->line('');
        $this->line('Directory structure created:');
        $this->line("📁 modules/{$contextName}/");
        $this->line('  📁 Application/');
        $this->line('    📁 Commands/');
        $this->line('    📁 Queries/');
        $this->line('    📁 Listeners/');
        $this->line('    📁 Projectors/');
        $this->line('    📁 DTOs/');
        $this->line('  📁 Domain/');
        $this->line('    📁 Entities/');
        $this->line('    📁 ValueObjects/');
        $this->line('    📁 Events/');
        $this->line('    📁 Repositories/');
        $this->line('    📁 Services/');
        $this->line('    📁 Exceptions/');
        $this->line('  📁 Infrastructure/');
        $this->line('    📁 Persistence/');
        $this->line('      📁 Eloquent/');
        $this->line('    📁 Http/');
        $this->line('      📁 Controllers/');
        $this->line('    📁 Providers/');
        $this->line('');
        $this->line('Next steps:');
        $this->line("• php artisan ddd:entity {$contextName} <EntityName>");
        $this->line("• php artisan ddd:value-object {$contextName} <ValueObjectName>");
        $this->line("• php artisan ddd:repository {$contextName} <EntityName>");

        return 0;
    }

    /**
     * Create the directory structure for the bounded context.
     */
    private function createDirectoryStructure(string $contextName): void
    {
        $basePath = $this->getContextPath($contextName);

        $directories = [
            // Application layer
            'Application/Commands',
            'Application/Queries',
            'Application/Listeners',
            'Application/Projectors',
            'Application/DTOs',
            
            // Domain layer
            'Domain/Entities',
            'Domain/ValueObjects',
            'Domain/Events',
            'Domain/Repositories',
            'Domain/Services',
            'Domain/Exceptions',
            
            // Infrastructure layer
            'Infrastructure/Persistence/Eloquent',
            'Infrastructure/Http/Controllers',
            'Infrastructure/Providers',
        ];

        foreach ($directories as $directory) {
            $this->ensureDirectoryExists("{$basePath}/{$directory}");
        }
    }

    /**
     * Create base files for the bounded context.
     */
    private function createBaseFiles(string $contextName): void
    {
        $replacements = $this->getCommonReplacements($contextName);

        // Create Service Provider
        $this->generateFromStub(
            $this->getStubPath('context-service-provider'),
            $this->getContextPath($contextName) . "/Infrastructure/Providers/{$contextName}ServiceProvider.php",
            $replacements
        );

        // Create base aggregate root
        $this->generateFromStub(
            $this->getStubPath('aggregate-root'),
            $this->getContextPath($contextName) . "/Domain/Entities/AggregateRoot.php",
            $replacements
        );

        // Create domain exception
        $this->generateFromStub(
            $this->getStubPath('domain-exception'),
            $this->getContextPath($contextName) . "/Domain/Exceptions/{$contextName}DomainException.php",
            $replacements
        );

        // Create domain event base
        $this->generateFromStub(
            $this->getStubPath('domain-event'),
            $this->getContextPath($contextName) . "/Domain/Events/DomainEvent.php",
            $replacements
        );

        // Create base controller
        $this->generateFromStub(
            $this->getStubPath('base-controller'),
            $this->getContextPath($contextName) . "/Infrastructure/Http/Controllers/BaseController.php",
            $replacements
        );

        // Create .gitkeep files for empty directories
        $this->createGitKeepFiles($contextName);
    }

    /**
     * Create .gitkeep files for empty directories.
     */
    private function createGitKeepFiles(string $contextName): void
    {
        $basePath = $this->getContextPath($contextName);
        
        $emptyDirectories = [
            'Application/Commands',
            'Application/Queries',
            'Application/Listeners',
            'Application/Projectors',
            'Application/DTOs',
            'Domain/Services',
            'Infrastructure/Persistence/Eloquent',
        ];

        foreach ($emptyDirectories as $directory) {
            $this->files->put("{$basePath}/{$directory}/.gitkeep", '');
        }
    }
}