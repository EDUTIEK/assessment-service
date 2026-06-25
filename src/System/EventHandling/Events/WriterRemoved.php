<?php

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event is raised when a writer is removed from an assessment
 *
 * - delete data of the writer
 * - delete correction assignments of the writer (triggers AssignmentRemoved)
 */
readonly class WriterRemoved implements Event
{
    public function __construct(
        private int $writer_id,
        private int $ass_id,
    ) {
    }

    public function getWriterId(): int
    {
        return $this->writer_id;
    }

    public function getAssId(): int
    {
        return $this->ass_id;
    }
}
