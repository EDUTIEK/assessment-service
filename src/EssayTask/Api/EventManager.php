<?php

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\Event\AbstractEventManager;
use Edutiek\AssessmentService\Assessment\Api\Internal;

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

    }
}