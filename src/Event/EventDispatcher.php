<?php

namespace Edutiek\AssessmentService\Event;

interface EventDispatcher
{
    public function initHandler() : void;

    public function dispatchEvent(Event $event): void;
}