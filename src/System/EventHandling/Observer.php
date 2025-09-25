<?php

namespace Edutiek\AssessmentService\System\EventHandling;

use Closure;

interface Observer
{
    /**
     * Register an event handler
     * The handler created lazy when needed for an event
     *
     * @param class-string<Handler> $handler handler class name
     * @param Closure(): Handler $init handler creation function
     */
    public function registerHandler(string $handler, Closure $init): void;

    /**
     * Call the registered handlers for an event
     */
    public function callHandlers(Event $event): void;
}
