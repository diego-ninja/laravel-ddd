<?php

namespace App\Console\Commands\DDD;

use Illuminate\Support\Str;

class MakeEventCommand extends BaseDDDCommand
{
    protected $signature = 'ddd:event {context : The bounded context name} {name : The event name} {--listener : Also create event listener}';
    protected $description = 'Create a new DDD domain event';

    public function handle(): int
    {
        $contextName = $this->argument('context');
        $eventName = $this->argument('name');
        $createListener = $this->option('listener');

        // Validate context name
        if (!$this->validateContextName($contextName)) {
            $this->error('Context name must start with uppercase letter and contain only letters and numbers.');
            return 1;
        }

        // Validate event name
        if (!$this->validateEventName($eventName)) {
            $this->error('Event name must start with uppercase letter and contain only letters and numbers.');
            $this->line('Event names should use past tense (e.g., OrderWasPlaced, CustomerWasCreated)');
            return 1;
        }

        // Check if context exists
        if (!$this->contextExists($contextName)) {
            $this->error("Context '{$contextName}' does not exist. Create it first with: php artisan ddd:context {$contextName}");
            return 1;
        }

        // Check if event already exists
        if ($this->eventExists($contextName, $eventName)) {
            $this->error("Event '{$eventName}' already exists in context '{$contextName}'!");
            return 1;
        }

        // Validate event naming convention (should be past tense)
        if (!$this->isPastTense($eventName)) {
            $this->warn("Event name '{$eventName}' should be in past tense (e.g., OrderWasPlaced, CustomerWasCreated)");
            if (!$this->confirm('Continue anyway?')) {
                return 1;
            }
        }

        $this->info("Creating domain event: {$eventName} in context: {$contextName}");

        // Create event file
        $this->createEventFile($contextName, $eventName);

        // Create listener if requested
        if ($createListener) {
            $this->createEventListener($contextName, $eventName);
        }

        $this->info("✅ Domain event '{$eventName}' created successfully in '{$contextName}' context!");
        $this->line('');
        $this->line('Files created:');
        $this->line("📁 modules/{$contextName}/Domain/Events/{$eventName}.php");
        
        if ($createListener) {
            $this->line("📁 modules/{$contextName}/Application/Listeners/{$eventName}Listener.php");
        }
        
        $this->line('');
        $this->line('Usage example:');
        $this->line("// In your entity");
        $this->line("\$this->record(new {$eventName}(\$this->id()->value()));");
        $this->line('');
        $this->line('Next steps:');
        if (!$createListener) {
            $this->line("• php artisan ddd:event {$contextName} {$eventName} --listener");
        }
        $this->line("• Register event listener in {$contextName}ServiceProvider");
        $this->line("• Dispatch events after saving aggregates");

        return 0;
    }

    /**
     * Validate event name.
     */
    private function validateEventName(string $eventName): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $eventName);
    }

    /**
     * Check if event exists.
     */
    private function eventExists(string $context, string $eventName): bool
    {
        $eventPath = $this->getContextPath($context) . "/Domain/Events/{$eventName}.php";
        return $this->files->exists($eventPath);
    }

    /**
     * Check if event name is in past tense (simple heuristic).
     */
    private function isPastTense(string $eventName): bool
    {
        $pastTenseIndicators = [
            'Was', 'Were', 'Had', 'Did', 'Created', 'Updated', 'Deleted', 
            'Placed', 'Cancelled', 'Shipped', 'Completed', 'Started',
            'Finished', 'Approved', 'Rejected', 'Sent', 'Received'
        ];

        foreach ($pastTenseIndicators as $indicator) {
            if (str_contains($eventName, $indicator)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Create the domain event file.
     */
    private function createEventFile(string $contextName, string $eventName): void
    {
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $eventName),
            [
                '{{ eventName }}' => $eventName,
                '{{ eventNameSnake }}' => Str::snake($eventName),
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('domain-event-concrete'),
            $this->getContextPath($contextName) . "/Domain/Events/{$eventName}.php",
            $replacements
        );
    }

    /**
     * Create event listener.
     */
    private function createEventListener(string $contextName, string $eventName): void
    {
        $listenerName = $eventName . 'Listener';
        
        $replacements = array_merge(
            $this->getCommonReplacements($contextName, $listenerName),
            [
                '{{ eventName }}' => $eventName,
                '{{ eventNameSnake }}' => Str::snake($eventName),
                '{{ listenerName }}' => $listenerName,
            ]
        );

        $this->generateFromStub(
            $this->getStubPath('event-listener'),
            $this->getContextPath($contextName) . "/Application/Listeners/{$listenerName}.php",
            $replacements
        );
    }
}