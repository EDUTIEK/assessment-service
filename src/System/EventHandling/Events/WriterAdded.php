<?php

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event must be raised when a writer is added to an assessment
 */
readonly class WriterAdded implements Event
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
