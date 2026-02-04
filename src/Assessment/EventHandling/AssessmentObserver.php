<?php

namespace Edutiek\AssessmentService\Assessment\EventHandling;

use Edutiek\AssessmentService\Assessment\Api\Internal;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\EventHandling\AbstractObserver;
use Edutiek\AssessmentService\Assessment\LogEntry;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterFullService;

class AssessmentObserver extends AbstractObserver
{
    public function __construct(
        int $ass_id,
        int $user_id,
        Internal $internal,
        Repositories $repos
    ) {
        $this->registerHandler(OnWritingContentChanged::class, fn() => new OnWritingContentChanged(
            $user_id,
            $internal->writer($ass_id, $user_id),
        ));
        $this->registerHandler(OnAssignmentRemoved::class, fn() => new OnAssignmentRemoved(
            $user_id,
            $internal->writer($ass_id, $user_id),
        ));
        $this->registerHandler(OnWriterRemoved::class, fn() => new OnWriterRemoved(
            $repos
        ));
    }
}
