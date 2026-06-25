<?php

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event is raised when a corrector to writer assignment is removed
 *
 * - delete all dependent correction data
 */
readonly class AssignmentRemoved implements Event
{
    public function __construct(
        private int $task_id,
        private int $writer_id,
        private int $corrector_id,
        private bool $was_stitch,
        private bool $was_authorized
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

    public function wasStitch(): bool
    {
        return $this->was_stitch;
    }

    public function wasAuthorized(): bool
    {
        return $this->was_authorized;
    }
}
