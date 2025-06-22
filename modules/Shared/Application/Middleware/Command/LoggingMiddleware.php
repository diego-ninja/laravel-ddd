<?php

namespace Modules\Shared\Application\Middleware\Command;

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
        $commandName = $message::class;
        $commandId = $this->generateCommandId();

        $this->logger->info('Command execution started', [
            'command_id' => $commandId,
            'command_name' => $commandName,
            'command_data' => $this->sanitizeCommandData($message),
            'timestamp' => now()->toISOString()
        ]);

        try {
            $result = $next($message);

            $this->logger->info('Command execution completed', [
                'command_id' => $commandId,
                'command_name' => $commandName,
                'status' => 'success',
                'execution_time' => $this->getExecutionTime(),
                'timestamp' => now()->toISOString()
            ]);

            return $result;

        } catch (Throwable $exception) {
            $this->logger->error('Command execution failed', [
                'command_id' => $commandId,
                'command_name' => $commandName,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
                'timestamp' => now()->toISOString()
            ]);

            throw $exception;
        }
    }

    private function generateCommandId(): string
    {
        return uniqid('cmd_', true);
    }

    private function sanitizeCommandData(object $command): array
    {
        $reflection = new \ReflectionClass($command);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPublic()) {
                $value = $property->getValue($command);

                // Sanitize sensitive data
                if (str_contains(strtolower($property->getName()), 'password')) {
                    $value = '[REDACTED]';
                }

                $data[$property->getName()] = $value;
            }
        }

        return $data;
    }

    private function getExecutionTime(): float
    {
        return microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? microtime(true));
    }
}
