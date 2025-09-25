<?php

namespace Edutiek\AssessmentService\System\EventHandling;

interface Handler
{
    /**
     * Get the events this handler is prepared for
     * @return class-string[] events this handler reacts on
     */
    public static function events(): array;

    /**
     * Execute actions for a raised event
     * Parameters are taken from the specific event class
     */
    public function handle(Event $event): void;
}
