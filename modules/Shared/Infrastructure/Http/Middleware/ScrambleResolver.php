<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Http\Middleware;

use KentarouTakeda\Laravel\OpenApiValidator\ResolverInterface;
use Illuminate\Support\Facades\File;

class ScrambleResolver implements ResolverInterface
{
    /**
     * @param array<string,string> $options
     */
    public function getJson(array $options): string
    {
        $specPath = $this->getScrambleSpecPath();
        
        if (!File::exists($specPath)) {
            throw new \RuntimeException("OpenAPI spec file not found at: {$specPath}");
        }
        
        $content = File::get($specPath);
        
        if (empty($content)) {
            throw new \RuntimeException("OpenAPI spec file is empty: {$specPath}");
        }
        
        return $content;
    }
    
    private function getScrambleSpecPath(): string
    {
        // Get the Scramble export path from config
        $scrambleExportPath = config('scramble.export_path', 'api.json');
        $scrambleSpecPath = base_path($scrambleExportPath);
        
        if (File::exists($scrambleSpecPath)) {
            return $scrambleSpecPath;
        }
        
        // Fallback to storage path
        $storageSpecPath = storage_path('api-docs/api-docs.json');
        if (File::exists($storageSpecPath)) {
            return $storageSpecPath;
        }
        
        // Final fallback to project root
        return base_path('openapi.json');
    }
}