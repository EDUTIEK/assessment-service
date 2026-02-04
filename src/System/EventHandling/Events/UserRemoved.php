<?php

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event must be raised when a writer is removed from an assessment
 */
readonly class UserRemoved implements Event
{
    public function __construct(
        private int $user_id
    ) {
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }
}
