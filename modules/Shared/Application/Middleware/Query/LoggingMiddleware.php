<?php

namespace Modules\Shared\Application\Middleware\Query;

use Modules\Shared\Application\Contracts\Middleware;
use Psr\Log\LoggerInterface;
use Throwable;

final readonly class LoggingMiddleware implements Middleware
{
    public function __construct(
        private LoggerInterface $logger
    ) {}

    /**
     * @throws Throwable
     */
    public function handle(object $message, \Closure $next): mixed
    {
        $queryName = $message::class;
        $queryId = $this->generateQueryId();
        $startTime = microtime(true);

        $this->logger->debug('Query execution started', [
            'query_id' => $queryId,
            'query_name' => $queryName,
            'query_data' => $this->extractQueryData($message),
            'timestamp' => now()->toISOString()
        ]);

        try {
            $result = $next($message);
            $executionTime = microtime(true) - $startTime;

            $this->logger->debug('Query execution completed', [
                'query_id' => $queryId,
                'query_name' => $queryName,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'result_count' => $this->getResultCount($result),
                'timestamp' => now()->toISOString()
            ]);

            return $result;

        } catch (Throwable $exception) {
            $this->logger->error('Query execution failed', [
                'query_id' => $queryId,
                'query_name' => $queryName,
                'error' => $exception->getMessage(),
                'timestamp' => now()->toISOString()
            ]);

            throw $exception;
        }
    }

    private function generateQueryId(): string
    {
        return uniqid('qry_', true);
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

    private function getResultCount(mixed $result): ?int
    {
        if (is_array($result)) {
            return count($result);
        }

        if (is_object($result) && method_exists($result, 'count')) {
            return $result->count();
        }

        return null;
    }
}
