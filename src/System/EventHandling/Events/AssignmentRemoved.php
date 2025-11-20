<?php

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event must be raised when a corrector to writer assignment is removed
 */
readonly class AssignmentRemoved implements Event
{
    public function __construct(
        private int $task_id,
        private int $writer_id,
        private int $corrector_id
    ) {
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getWriterId(): int
    {
        return $this->writer_id;
    }

    public function getCorrectorId(): int
    {
        return $this->corrector_id;
    }
}
