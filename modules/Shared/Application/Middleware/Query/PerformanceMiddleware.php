<?php

namespace Modules\Shared\Application\Middleware\Query;

use Modules\Shared\Application\Contracts\Middleware;
use Psr\Log\LoggerInterface;

final readonly class PerformanceMiddleware implements Middleware
{
    private const SLOW_QUERY_THRESHOLD_MS = 1000; // 1 second

    public function __construct(
        private LoggerInterface $logger
    ) {}

    public function handle(object $message, \Closure $next): mixed
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $result = $next($message);

        $executionTime = microtime(true) - $startTime;
        $memoryUsage = memory_get_usage(true) - $startMemory;
        $executionTimeMs = round($executionTime * 1000, 2);

        if ($executionTimeMs > self::SLOW_QUERY_THRESHOLD_MS) {
            $this->logger->warning('Slow query detected', [
                'query_name' => $message::class,
                'execution_time_ms' => $executionTimeMs,
                'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'threshold_ms' => self::SLOW_QUERY_THRESHOLD_MS
            ]);
        }

        return $result;
    }
}
