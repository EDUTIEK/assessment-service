<?php

namespace Edutiek\AssessmentService\EssayTask\EventHandling;

use Edutiek\AssessmentService\EssayTask\Api\Internal;
use Edutiek\AssessmentService\System\EventHandling\AbstractObserver;

class Observer extends AbstractObserver
{
    public function __construct(
        int $ass_id,
        int $user_id,
        private readonly Internal $internal
    ) {
    }
}
