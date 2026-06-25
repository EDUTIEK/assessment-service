<?php

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event be raised when a user is removed from the hosting system
 *
 * - remove the user from pending notifications
 * - remove the user as writer from all assessments
 * - remove the user as corrector from all assessments
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
