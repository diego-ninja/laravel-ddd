<?php

namespace App\Console\Commands\DDD;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

abstract class BaseDDDCommand extends Command
{
    protected Filesystem $files;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Get the modules path.
     */
    protected function getModulesPath(): string
    {
        return base_path('modules');
    }

    /**
     * Get the path for a specific bounded context.
     */
    protected function getContextPath(string $context): string
    {
        return $this->getModulesPath() . '/' . $context;
    }

    /**
     * Ensure directory exists.
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (!$this->files->isDirectory($path)) {
            $this->files->makeDirectory($path, 0755, true);
        }
    }

    /**
     * Generate file from stub.
     */
    protected function generateFromStub(string $stubPath, string $destinationPath, array $replacements = []): bool
    {
        if (!$this->files->exists($stubPath)) {
            $this->error("Stub file not found: {$stubPath}");
            return false;
        }

        $stubContent = $this->files->get($stubPath);
        
        foreach ($replacements as $search => $replace) {
            $stubContent = str_replace($search, $replace, $stubContent);
        }

        $this->ensureDirectoryExists(dirname($destinationPath));
        $this->files->put($destinationPath, $stubContent);

        return true;
    }

    /**
     * Get stub path.
     */
    protected function getStubPath(string $stubName): string
    {
        return base_path("stubs/ddd/{$stubName}.stub");
    }

    /**
     * Get common replacements for stubs.
     */
    protected function getCommonReplacements(string $context, string $name = ''): array
    {
        return [
            '{{ context }}' => $context,
            '{{ contextLower }}' => Str::lower($context),
            '{{ contextCamel }}' => Str::camel($context),
            '{{ contextSnake }}' => Str::snake($context),
            '{{ name }}' => $name,
            '{{ nameLower }}' => Str::lower($name),
            '{{ nameCamel }}' => Str::camel($name),
            '{{ nameSnake }}' => Str::snake($name),
            '{{ namespace }}' => "Modules\\{$context}",
        ];
    }

    /**
     * Check if context exists.
     */
    protected function contextExists(string $context): bool
    {
        return $this->files->isDirectory($this->getContextPath($context));
    }

    /**
     * Validate context name.
     */
    protected function validateContextName(string $context): bool
    {
        return preg_match('/^[A-Z][a-zA-Z0-9]*$/', $context);
    }
}