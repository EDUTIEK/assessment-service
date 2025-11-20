<?php

namespace Edutiek\AssessmentService\Task\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\AbstractObserver;
use Edutiek\AssessmentService\Task\Api\Internal;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\EventHandling\OnWritingContentChanged;

class Observer extends AbstractObserver
{
    public function __construct(
        int $ass_id,
        int $user_id,
        Internal $internal,
        Repositories $repos
    ) {
        $this->registerHandler(OnWriterRemoved::class, fn() => new OnWriterRemoved(
            $internal->correctorAssignments($ass_id, $user_id),
            $repos
        ));

        $this->registerHandler(OnAssignmentRemoved::class, fn() => new OnAssignmentRemoved(
            $repos
        ));
    }
}
