<?php

namespace Edutiek\AssessmentService\Assessment\LogEntry;

use Edutiek\AssessmentService\Event\Event;
use Edutiek\AssessmentService\Assessment\Api\Internal;
use Edutiek\AssessmentService\EssayTask\CorrectorSummary\AuthorizationChangeEvent;

class EventHandler implements \Edutiek\AssessmentService\Event\EventHandler
{
    public function __construct(private readonly Internal $internal)
    {

    }

    public static function events(): array
    {
        return [AuthorizationChangeEvent::class];
    }

    public function handleEvent(Event $event): void
    {
        switch($event::class) {
            case AuthorizationChangeEvent::class:
                $this->authorizationChange($event);
                break;
        };
    }

    private function authorizationChange(AuthorizationChangeEvent $event)
    {
        #$log_entry = $this->internal->logEntry(0);
        #$log_entry->addEntry();
    }
}