<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\EventHandling;

interface DispatcherFactory
{
    /**
     * Dispatcher for events raised in an assessment
     * Provided as a dependency for the service components
     *
     * @param int $ass_id   id of the assessment
     * @param int $user_id id of the active user
     */
    public function assessmentDispatcher(int $ass_id, int $user_id): Dispatcher;

    /**
     * Dispatcher for events raised in the system outside an assessment
     * Provided as a dependency for the client system
     *
     * @param int $user_id id of the active user
    */
    public function systemDispatcher(int $user_id): Dispatcher;
}
