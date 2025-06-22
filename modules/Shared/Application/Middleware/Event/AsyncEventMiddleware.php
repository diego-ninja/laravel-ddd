<?php

namespace Modules\Shared\Application\Middleware\Event;

use Illuminate\Bus\Dispatcher as JobDispatcher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Closure;
use Modules\Shared\Application\Contracts\EventBus;
use Modules\Shared\Application\Contracts\Middleware;
use Modules\Shared\Domain\Contracts\DomainEvent;

final readonly class AsyncEventMiddleware implements Middleware
{
    public function __construct(
        private JobDispatcher $jobDispatcher
    ) {}

    /**
     * @param object<DomainEvent> $message
     * @param Closure $next
     * @return mixed
     */
    public function handle(object $message, Closure $next): mixed
    {
        /** @var DomainEvent $message*/
        if ($this->shouldProcessAsync($message)) {
            $this->jobDispatcher->dispatch(new class($message) implements ShouldQueue {
                use Dispatchable, Queueable;

                public function __construct(
                    private readonly DomainEvent $event
                ) {}

                public function handle(): void
                {
                    // Re-dispatch the event through the synchronous pipeline
                    app(EventBus::class)->publish($this->event);
                }
            });
            return null;
        }

        return $next($message);
    }

    private function shouldProcessAsync(object $event): bool
    {
        return method_exists($event, 'shouldQueue') && $event->shouldQueue();
    }
}
