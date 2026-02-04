<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\System\EventHandling\ObserverFactory;
use Edutiek\AssessmentService\Assessment\EventHandling\AssessmentObserver;
use Edutiek\AssessmentService\Assessment\EventHandling\SystemObserver;

readonly class ForEvents implements ObserverFactory
{
    public function __construct(
        private Internal $internal
    ) {
    }

    public function assessmentObserver(int $ass_id, int $user_id): ?AssessmentObserver
    {
        return $this->internal->assessmentObserver($ass_id, $user_id);
    }

    public function systemObserver(int $user_id): ?SystemObserver
    {
        return $this->internal->systemObserver($user_id);
    }
}
