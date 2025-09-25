<?php

namespace Edutiek\AssessmentService\System\EventHandling;

interface Dispatcher
{
    /**
     * Notify all observers about a raised event
     */
    public function dispatchEvent(Event $event): void;

    /**
     * Add an observer that should be called for event
     */
    public function addObserver(Observer $observer): void;

    /**
     * Remove an observer that would be called for an event, too
     */
    public function removeObserver(Observer $observer): void;
}
