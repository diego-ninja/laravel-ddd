<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeCommandCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:command {context : The bounded context name} {name : The command name} {--no-validator : Skip creating command validator}';
    protected $description = 'Create a new CQRS command with handler and validator';

    public function handle(): int
    {
        $contextName = $this->argument('context');
        $commandName = $this->argument('name');
        $skipValidator = $this->option('no-validator');

        // Validate context name
        if (!$this->validateContextName($contextName)) {
            $this->error('Context name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Validate command name
        if (!$this->validateCommandName($commandName)) {
            $this->error('Command name must start with uppercase letter and contain only letters and numbers.');
            $this->line('Command names should be imperative (e.g., PlaceOrder, CreateCustomer, UpdateProduct)');
            return 1;
        }

        // Check if context exists
        if (!$this->contextExists($contextName)) {
            $this->error("Context '{$contextName}' does not exist. Create it first with: php artisan ddd:context {$contextName}");
            return 1;
        }

        // Check if command already exists
        if ($this->commandExists($contextName, $commandName)) {
            $this->error("Command '{$commandName}' already exists in context '{$contextName}'!");
            return 1;
        }

        // Validate command naming convention (should be imperative)
        if (!$this->isImperative($commandName)) {
            $this->warn("Command name '{$commandName}' should be imperative (e.g., PlaceOrder, CreateCustomer, UpdateProduct)");
            if (!$this->confirm('Continue anyway?')) {
                return 1;
            }
        }

        $this->info("Creating CQRS command: {$commandName} in context: {$contextName}");

        // Create command directory structure
        $this->createCommandDirectory($contextName, $commandName);

        // Create command files
        $this->createCommandFile($contextName, $commandName);
        $this->createCommandHandlerFile($contextName, $commandName);
        
        if (!$skipValidator) {
            $this->createCommandValidatorFile($contextName, $commandName);
        }

        $this->info("✅ CQRS command '{$commandName}' created successfully in '{$contextName}' context!");
        $this->line('');
        $this->line('Files created:');
        $this->line("📁 modules/{$contextName}/Application/Commands/{$commandName}/");
        $this->line("  📄 {$commandName}Command.php");
        $this->line("  📄 {$commandName}CommandHandler.php");
        
        if (!$skipValidator) {
            $this->line("  📄 {$commandName}CommandValidator.php");
        }
        
        $this->line('');
        $this->line('Usage example:');
        $this->line("// Create and dispatch command");
        $this->line("\$command = new {$commandName}Command(");
        $this->line("    // Add command parameters");
        $this->line(");");
        $this->line("");
        $this->line("// Dispatch via Command Bus");
        $this->line("\$commandBus->dispatch(\$command);");
        $this->line('');
        $this->line('Next steps:');
        $this->line("• Add validation rules to {$commandName}CommandValidator");
        $this->line("• Implement business logic in {$commandName}CommandHandler");
        $this->line("• Create corresponding domain events if needed");
        $this->line("• Handlers are auto-discovered and registered with the Command Bus");

        return 0;
    }

    /**
     * Validate command name.
     */
    private function validateCommandName(string $commandName): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $commandName);
    }

    /**
     * Check if command exists.
     */
    private function commandExists(string $context, string $commandName): bool
    {
        $commandPath = $this->getContextPath($context) . "/Application/Commands/{$commandName}/{$commandName}Command.php";
        return $this->files->exists($commandPath);
    }

    /**
     * Check if command name is imperative (simple heuristic).
     */
    private function isImperative(string $commandName): bool
    {
        $imperativeIndicators = [
            'Create', 'Update', 'Delete', 'Place', 'Cancel', 'Ship', 
            'Complete', 'Start', 'Finish', 'Approve', 'Reject', 
            'Send', 'Receive', 'Process', 'Generate', 'Add', 'Remove',
            'Set', 'Change', 'Move', 'Transfer', 'Assign', 'Register'
        ];

        foreach ($imperativeIndicators as $indicator) {
            if (str_starts_with($commandName, $indicator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create command directory structure.
     */
    private function createCommandDirectory(string $contextName, string $commandName): void
    {
        $commandDir = $this->getContextPath($contextName) . "/Application/Commands/{$commandName}";
        $this->ensureDirectoryExists($commandDir);
    }

    /**
     * Create the command DTO file.
     */
    private function createCommandFile(string $contextName, string $commandName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $commandName),
            [
                '{{ commandName }}' => $commandName,
                '{{ commandVariable }}' => $this->getVariableName($commandName),
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('command'),
            $this->getContextPath($contextName) . "/Application/Commands/{$commandName}/{$commandName}Command.php",
            $replacements
        );
    }

    /**
     * Create the command handler file.
     */
    private function createCommandHandlerFile(string $contextName, string $commandName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $commandName),
            [
                '{{ commandName }}' => $commandName,
                '{{ commandVariable }}' => $this->getVariableName($commandName),
                '{{ handlerName }}' => $commandName . 'CommandHandler',
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('command-handler'),
            $this->getContextPath($contextName) . "/Application/Commands/{$commandName}/{$commandName}CommandHandler.php",
            $replacements
        );
    }

    /**
     * Create the command validator file.
     */
    private function createCommandValidatorFile(string $contextName, string $commandName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $commandName),
            [
                '{{ commandName }}' => $commandName,
                '{{ commandVariable }}' => $this->getVariableName($commandName),
                '{{ validatorName }}' => $commandName . 'CommandValidator',
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('command-validator'),
            $this->getContextPath($contextName) . "/Application/Commands/{$commandName}/{$commandName}CommandValidator.php",
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