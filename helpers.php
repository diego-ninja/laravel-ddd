<?php

if (! function_exists('module_path')) {
    /**
     * Get the path to a module directory or file.
     * 
     * If called from within a module context (e.g., from a ServiceProvider),
     * the module name will be auto-detected from the calling file path.
     * 
     * @param string $path The relative path within the module
     * @param string|null $module The module name (auto-detected if null)
     * @return string The full path to the module resource
     * 
     * @example
     * // From within UsersServiceProvider:
     * module_path('routes/api.php') // Auto-detects "Users" module
     * 
     * // Explicit module specification:
     * module_path('routes/api.php', 'Auth')
     */
    function module_path(string $path, ?string $module = null): string
    {
        // If module is not provided, try to auto-detect from calling context
        if ($module === null) {
            $module = detect_current_module();
        }
        
        // If still no module detected, throw an exception
        if ($module === null) {
            throw new InvalidArgumentException(
                'Could not auto-detect module. Please provide the module name explicitly: module_path("' . $path . '", "ModuleName")'
            );
        }
        
        return base_path(sprintf("modules/%s/%s", $module, ltrim($path, '/')));
    }
}

if (! function_exists('detect_current_module')) {
    /**
     * Detect the current module based on the calling file's path.
     * 
     * @return string|null The module name or null if not detected
     */
    function detect_current_module(): ?string
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        
        // Look through the backtrace to find a file in the modules directory
        foreach ($backtrace as $trace) {
            if (!isset($trace['file'])) {
                continue;
            }
            
            $file = $trace['file'];
            
            // Check if the file is within a module directory
            if (preg_match('#/modules/([^/]+)/#', $file, $matches)) {
                return $matches[1];
            }
        }
        
        return null;
    }
}
