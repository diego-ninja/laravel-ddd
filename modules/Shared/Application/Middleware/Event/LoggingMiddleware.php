<?php

namespace Modules\Shared\Application\Middleware\Event;

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
        $eventName = $message::class;
        $eventId = $this->generateEventId();

        $this->logger->info('Domain event published', [
            'event_id' => $eventId,
            'event_name' => $eventName,
            'event_data' => $this->extractEventData($message),
            'timestamp' => now()->toISOString()
        ]);

        try {
            $result = $next($message);

            $this->logger->info('Domain event processed successfully', [
                'event_id' => $eventId,
                'event_name' => $eventName,
                'timestamp' => now()->toISOString()
            ]);

            return $result;

        } catch (Throwable $exception) {
            $this->logger->error('Domain event processing failed', [
                'event_id' => $eventId,
                'event_name' => $eventName,
                'error' => $exception->getMessage(),
                'timestamp' => now()->toISOString()
            ]);

            throw $exception;
        }
    }

    private function generateEventId(): string
    {
        return uniqid('evt_', true);
    }

    private function extractEventData(object $event): array
    {
        $reflection = new \ReflectionClass($event);
        $data = [];

        foreach ($reflection->getProperties() as $property) {
            if ($property->isPublic()) {
                $data[$property->getName()] = $property->getValue($event);
            }
        }

        return $data;
    }
}
