<?php

namespace Edutiek\AssessmentService\Event;

use ILIAS;

abstract class AbstractEventManager implements EventObserver, EventDispatcher
{
    private array $observers = [];
    /**
     * @var string[string]
     */
    private array $event_map = [];
    /**
     * @var \Pimple\Container
     */
    private \Pimple\Container $handler;

    public function __construct()
    {
        $this->handler = new \Pimple\Container();
    }

    protected function registerHandler($handler, \Closure $init)
    {
        array_map(fn(string $event) => $this->event_map[$event][] = $handler, $handler::events());
        $this->handler[$handler] = $init;
    }

    public function dispatchEvent(Event $event) : void
    {
        $this->update($event);
        $this->notifyObservers($event);
    }

    public function addObserver(EventObserver $observer) : void
    {
        $this->observers[] = $observer;
    }

    public function removeObserver(EventObserver $observer): void
    {
        $key = array_search($observer, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }

    public function notifyObservers(Event $event) : void
    {
        array_map(fn (EventObserver $observer) => $observer->update($event), $this->observers);
    }

    public function update(Event $event) : void
    {
        $handler = array_map(fn (string $handler) => $this->handler[$handler], $this->event_map[$event::class] ?? []);
        array_map(fn (EventHandler $handler) => $handler->handerEvent($event), $handler);
    }
}
