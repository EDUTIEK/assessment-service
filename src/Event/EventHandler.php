<?php

namespace Edutiek\AssessmentService\Event;

interface EventHandler
{
    public static function events() : array;
    public function handleEvent(Event $event) : void;
}