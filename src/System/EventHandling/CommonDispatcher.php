<?php

namespace Edutiek\AssessmentService\System\EventHandling;

use Closure;

class CommonDispatcher implements Dispatcher
{
    /** @var Observer[] */
    private array $observers = [];

    public function addObserver(Observer $observer): void
    {
        $this->observers[] = $observer;
    }

    public function removeObserver(Observer $observer): void
    {
        $key = array_search($observer, $this->observers, true);
        if ($key !== false) {
            unset($this->observers[$key]);
        }
    }

    public function dispatchEvent(Event $event): void
    {
        array_map(fn(Observer $observer) => $observer->callHandlers($event), $this->observers);
    }
}
