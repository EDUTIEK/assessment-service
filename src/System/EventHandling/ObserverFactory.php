<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\EventHandling;

interface ObserverFactory
{
    /**
     * Observer for events occuring in an assessment
     */
    public function assessmentObserver(int $ass_id, int $user_id): ?Observer;

    /**
     * Observer for events occuring in the system
     */
    public function systemObserver(int $user_id): ?Observer;
}
