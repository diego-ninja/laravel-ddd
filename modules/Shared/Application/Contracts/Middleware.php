<?php

namespace Modules\Shared\Application\Contracts;

use Closure;

interface Middleware
{
    /**
     * Handle the message through the middleware pipeline.
     */
    public function handle(object $message, Closure $next): mixed;
}
