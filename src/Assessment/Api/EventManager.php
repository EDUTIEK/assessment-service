<?php

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Api;
use Edutiek\AssessmentService\Event\AbstractEventManager;
use Edutiek\AssessmentService\Assessment\LogEntry;

class EventManager extends AbstractEventManager
{
    public function __construct(
        private readonly Internal $internal
    ) {
        parent::__construct();
        $this->initHandler();
    }

    public function initHandler(): void
    {
        $this->registerHandler(LogEntry\EventHandler::class, fn() => new LogEntry\EventHandler($this->internal));
    }
}