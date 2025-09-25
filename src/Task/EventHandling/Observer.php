<?php

namespace Edutiek\AssessmentService\Task\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\AbstractObserver;
use Edutiek\AssessmentService\Task\Api\Internal;
use Edutiek\AssessmentService\Task\EventHandling\OnWritingContentChanged;

class Observer extends AbstractObserver
{
    public function __construct(
        int $ass_id,
        int $user_id,
        private readonly Internal $internal
    ) {
    }
}
