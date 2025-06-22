<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeControllerCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:controller {context : The bounded context name} {name : The controller name}';
    protected $description = 'Create a new DDD controller with CRUD methods';

    public function handle(): int
    {
        $contextName = $this->argument('context');
        $controllerName = $this->argument('name');

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

        // Validate and clean controller name
        $controllerName = $this->cleanControllerName($controllerName);

        if (!$this->validateControllerName($controllerName)) {
            $this->error('Controller name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        $filePath = $this->getContextPath($contextName) . "/Infrastructure/Http/Controllers/{$controllerName}Controller.php";

        // Check if controller already exists
        if ($this->files->exists($filePath)) {
            $this->error("Controller '{$controllerName}Controller' already exists in context '{$contextName}'!");
            return 1;
        }

        $this->info("Creating controller: {$controllerName}Controller in {$contextName} context");

        // Create the controller
        $this->createController($contextName, $controllerName, $filePath);

        $this->info("✅ Controller '{$controllerName}Controller' created successfully!");
        $this->line('');
        $this->line("📁 Created: modules/{$contextName}/Infrastructure/Http/Controllers/{$controllerName}Controller.php");
        $this->line('');
        $this->line('Next steps:');
        $this->line("• Add routes in your routes file");
        $this->line("• Create corresponding commands and queries:");
        $this->line("  php artisan ddd:command {$contextName} Create{$controllerName}");
        $this->line("  php artisan ddd:command {$contextName} Update{$controllerName}");
        $this->line("  php artisan ddd:command {$contextName} Delete{$controllerName}");
        $this->line("  php artisan ddd:query {$contextName} Get{$controllerName}List");
        $this->line("  php artisan ddd:query {$contextName} Get{$controllerName}ById");

        return 0;
    }

    /**
     * Create the controller file.
     */
    private function createController(string $contextName, string $controllerName, string $filePath): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName),
            [
                '{{ name }}' => $controllerName,
                '{{ nameLower }}' => Str::camel($controllerName),
                '{{ namePlural }}' => Str::plural(Str::lower($controllerName)),
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('controller'),
            $filePath,
            $replacements
        );
    }

    /**
     * Clean the controller name by removing "Controller" suffix if present.
     */
    private function cleanControllerName(string $name): string
    {
        return Str::studly(str_replace('Controller', '', $name));
    }

    /**
     * Validate controller name format.
     */
    private function validateControllerName(string $name): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $name);
    }
}