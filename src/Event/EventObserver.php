<?php

namespace Edutiek\AssessmentService\Event;

interface EventObserver
{
    public function addObserver(EventObserver $observer) : void;
    public function removeObserver(EventObserver $observer) : void;
    public function notifyObservers(Event $event) : void;
    public function update(Event $event) : void;
}