<?php

namespace Edutiek\AssessmentService\System\EventHandling;

use Closure;

abstract class AbstractObserver implements Observer
{
    /** @var array<class-string<Event>, class-string<Handler>[]> */
    private array $event_handlers = [];

    /** @var array<class-string<Handler>, Closure(): Handler> */
    private $handler_inits = [];

    /** @var array<class-string<Handler>, Handler> */
    private $handler_instances = [];

    /**
     * @param class-string<Handler> $handler handler class name
     * @param Closure(): Handler $init handler creation function
     */
    public function registerHandler(string $handler, Closure $init): void
    {
        array_map(
            fn(string $event) => $this->event_handlers[$event][] = $handler,
            $handler::events()
        );
        unset($this->handler_instances[$handler]);
        $this->handler_inits[$handler] = $init;
    }

    public function callHandlers(Event $event): void
    {
        array_map(fn(Handler $handler) => $handler->handle($event), $this->getHandlers($event));
    }

    /** @return Handler[] */
    private function getHandlers(Event $event): array
    {
        return array_map(
            function (string $handler_class) {
                return $this->handler_instances[$handler_class] ?? $this->handler_inits[$handler_class]();
            },
            $this->event_handlers[$event::class] ?? []
        );
    }
}
