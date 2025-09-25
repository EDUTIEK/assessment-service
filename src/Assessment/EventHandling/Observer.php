<?php

namespace Edutiek\AssessmentService\Assessment\EventHandling;

use Edutiek\AssessmentService\Assessment\Api\internal;
use Edutiek\AssessmentService\System\EventHandling\AbstractObserver;
use Edutiek\AssessmentService\Assessment\LogEntry;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterFullService;

class Observer extends AbstractObserver
{
    public function __construct(
        int $ass_id,
        int $user_id,
        private readonly Internal $internal,
    ) {
        $this->registerHandler(OnWritingContentChanged::class, fn() => new OnWritingContentChanged(
            $user_id,
            $this->internal->writer($ass_id, $user_id),
        ));
    }
}
