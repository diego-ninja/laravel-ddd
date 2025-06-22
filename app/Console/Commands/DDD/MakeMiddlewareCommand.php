<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeMiddlewareCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:middleware {context : The bounded context name} {name : The middleware name} {type : The middleware type (command, query, event)}';
    protected $description = 'Create a new DDD middleware for command, query, or event processing';

    private const AVAILABLE_TYPES = ['command', 'query', 'event'];

    public function handle(): int
    {
        $contextName = $this->argument('context');
        $middlewareName = $this->argument('name');
        $middlewareType = $this->argument('type');

        // Validate context name
        if (!$this->validateContextName($contextName)) {
            $this->error('Context name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Validate middleware name
        if (!$this->validateMiddlewareName($middlewareName)) {
            $this->error('Middleware name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Validate middleware type
        if (!in_array($middlewareType, self::AVAILABLE_TYPES)) {
            $this->error("Invalid middleware type '{$middlewareType}'. Available types: " . implode(', ', self::AVAILABLE_TYPES));
            return 1;
        }

        // Check if context exists
        if (!$this->contextExists($contextName)) {
            $this->error("Context '{$contextName}' does not exist. Create it first with: php artisan ddd:context {$contextName}");
            return 1;
        }

        // Check if middleware already exists
        if ($this->middlewareExists($contextName, $middlewareName, $middlewareType)) {
            $this->error("Middleware '{$middlewareName}' already exists in context '{$contextName}'!");
            return 1;
        }

        // Ensure middleware name follows convention
        if (!str_ends_with($middlewareName, 'Middleware')) {
            $middlewareName .= 'Middleware';
        }

        $this->info("Creating {$middlewareType} middleware: {$middlewareName} in context: {$contextName}");

        // Create middleware directory if needed
        $this->createMiddlewareDirectory($contextName, $middlewareType);

        // Create middleware file
        $this->createMiddlewareFile($contextName, $middlewareName, $middlewareType);

        $this->info("✅ {$middlewareType} middleware '{$middlewareName}' created successfully in '{$contextName}' context!");
        $this->line('');
        $this->line('File created:');
        $this->line("📁 modules/{$contextName}/Application/Middleware/{$this->getMiddlewareSubfolder($middlewareType)}/{$middlewareName}.php");
        $this->line('');
        $this->line('Usage example:');
        $this->showUsageExample($middlewareName, $middlewareType);
        $this->line('');
        $this->line('Next steps:');
        $this->line("• Implement middleware logic in {$middlewareName}");
        $this->line("• Register middleware in {$contextName}ServiceProvider");
        $this->line("• Configure middleware pipeline for {$middlewareType}s");

        return 0;
    }

    /**
     * Validate middleware name.
     */
    private function validateMiddlewareName(string $middlewareName): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $middlewareName);
    }

    /**
     * Check if middleware exists.
     */
    private function middlewareExists(string $context, string $middlewareName, string $type): bool
    {
        $subfolder = $this->getMiddlewareSubfolder($type);
        $middlewarePath = $this->getContextPath($context) . "/Application/Middleware/{$subfolder}/{$middlewareName}.php";
        return $this->files->exists($middlewarePath);
    }

    /**
     * Get middleware subfolder based on type.
     */
    private function getMiddlewareSubfolder(string $type): string
    {
        return match ($type) {
            'command' => 'Commands',
            'query' => 'Queries',
            'event' => 'Events',
        };
    }

    /**
     * Create middleware directory structure.
     */
    private function createMiddlewareDirectory(string $contextName, string $middlewareType): void
    {
        $subfolder = $this->getMiddlewareSubfolder($middlewareType);
        $middlewareDir = $this->getContextPath($contextName) . "/Application/Middleware/{$subfolder}";
        $this->ensureDirectoryExists($middlewareDir);
    }

    /**
     * Create the middleware file.
     */
    private function createMiddlewareFile(string $contextName, string $middlewareName, string $middlewareType): void
    {
        $subfolder = $this->getMiddlewareSubfolder($middlewareType);
        
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $middlewareName),
            [
                '{{ middlewareName }}' => $middlewareName,
                '{{ middlewareType }}' => $middlewareType,
                '{{ middlewareTypeTitle }}' => ucfirst($middlewareType),
                '{{ middlewareVariable }}' => $this->getVariableName($middlewareName),
            ]
        );

        $stubName = "middleware-{$middlewareType}";
        
        $this->generateFromStub(
            $this->getStubPath($stubName),
            $this->getContextPath($contextName) . "/Application/Middleware/{$subfolder}/{$middlewareName}.php",
            $replacements
        );
    }

    /**
     * Show usage example for the middleware.
     */
    private function showUsageExample(string $middlewareName, string $middlewareType): void
    {
        $examples = [
            'command' => [
                "// In {$middlewareType} pipeline:",
                "\$pipeline = [",
                "    {$middlewareName}::class,",
                "    // Other middleware...",
                "];",
                "",
                "// Register in ServiceProvider:",
                "\$this->app->bind('command.middleware.pipeline', \$pipeline);"
            ],
            'query' => [
                "// In {$middlewareType} pipeline:",
                "\$pipeline = [",
                "    {$middlewareName}::class,",
                "    // Other middleware...",
                "];",
                "",
                "// Register in ServiceProvider:",
                "\$this->app->bind('query.middleware.pipeline', \$pipeline);"
            ],
            'event' => [
                "// In {$middlewareType} pipeline:",
                "\$pipeline = [",
                "    {$middlewareName}::class,",
                "    // Other middleware...",
                "];",
                "",
                "// Register in ServiceProvider:",
                "\$this->app->bind('event.middleware.pipeline', \$pipeline);"
            ],
        ];

        foreach ($examples[$middlewareType] as $line) {
            $this->line($line);
        }
    }

    /**
     * Get variable name from class name.
     */
    private function getVariableName(string $className): string
    {
        return Str::camel($className);
    }
}