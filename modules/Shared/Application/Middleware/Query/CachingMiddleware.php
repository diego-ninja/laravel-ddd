<?php

namespace Modules\Shared\Application\Middleware\Query;

use Illuminate\Cache\CacheManager;
use Modules\Shared\Application\Contracts\Middleware;

final readonly class CachingMiddleware implements Middleware
{
    public function __construct(
        private CacheManager $cache
    ) {}

    public function handle(object $message, \Closure $next): mixed
    {
        $cacheKey = $this->generateCacheKey($message);
        $ttl = $this->getCacheTtl($message);

        if ($ttl <= 0) {
            return $next($message);
        }

        return $this->cache->remember($cacheKey, $ttl, function () use ($message, $next) {
            return $next($message);
        });
    }

    private function generateCacheKey(object $query): string
    {
        $className = str_replace('\\', '_', $query::class);
        $dataHash = md5(serialize($this->extractQueryData($query)));

        return "query_{$className}_{$dataHash}";
    }

    private function getCacheTtl(object $query): int
    {
        if (method_exists($query, 'getCacheTtl')) {
            return $query->getCacheTtl();
        }

        // Default cache time: 5 minutes
        return 300;
    }

    private function extractQueryData(object $query): array
    {
        $reflection = new \ReflectionClass($query);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPublic()) {
                $data[$property->getName()] = $property->getValue($query);
            }
        }

        return $data;
    }
}
