<?php

namespace Modules\Shared\Infrastructure\Support;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\File;
use ReflectionClass;
use ReflectionException;
use Modules\Shared\Application\Contracts\CommandHandler;
use Modules\Shared\Application\Contracts\QueryHandler;

class HandlerDiscovery
{
    /**
     * Discover all command handlers in the modules.
     */
    public static function discoverCommandHandlers(): array
    {
        $handlers = [];
        $modules = self::getModules();

        foreach ($modules as $module) {
            $commandPath = base_path("modules/{$module}/Application/Commands");
            if (!File::exists($commandPath)) {
                continue;
            }

            $handlers = array_merge($handlers, self::findHandlersInPath($commandPath, CommandHandler::class));
        }

        return $handlers;
    }

    /**
     * Discover all query handlers in the modules.
     */
    public static function discoverQueryHandlers(): array
    {
        $handlers = [];
        $modules = self::getModules();

        foreach ($modules as $module) {
            $queryPath = base_path("modules/{$module}/Application/Queries");
            if (!File::exists($queryPath)) {
                continue;
            }

            $handlers = array_merge($handlers, self::findHandlersInPath($queryPath, QueryHandler::class));
        }

        return $handlers;
    }

    /**
     * Get all available modules.
     */
    private static function getModules(): array
    {
        $modulesPath = base_path('modules');
        if (!File::exists($modulesPath)) {
            return [];
        }

        return collect(File::directories($modulesPath))
            ->map(fn($path) => basename($path))
            ->filter(fn($module) => $module !== 'Shared')
            ->toArray();
    }

    /**
     * Find handlers in a specific path that implement a given interface.
     */
    private static function findHandlersInPath(string $path, string $interface): array
    {
        $handlers = [];
        $files = File::allFiles($path);

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            try {
                $className = self::getClassNameFromFile($file->getPathname());
                if (!$className || !class_exists($className)) {
                    continue;
                }

                $reflection = new ReflectionClass($className);
                if ($reflection->implementsInterface($interface) && !$reflection->isAbstract()) {
                    $handlers[] = $className;
                }
            } catch (ReflectionException) {
                // Skip files that can't be reflected
                continue;
            }
        }

        return $handlers;
    }

    /**
     * Extract class name from file path.
     * @throws FileNotFoundException
     */
    private static function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);

        // Extract namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatches)) {
            $namespace = trim($namespaceMatches[1]);
        } else {
            return null;
        }

        // Extract class name
        if (preg_match('/class\s+(\w+)/', $content, $classMatches)) {
            $className = trim($classMatches[1]);
            return $namespace . '\\' . $className;
        }

        return null;
    }
}
