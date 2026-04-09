<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use DateTimeImmutable;
use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event is raised when the content provided by a writer is changed
 * Currently only raised when a PDF file is uploaded
 * Normal writing steps sent by the web app don't raise this event
 */
readonly class WritingContentChanged implements Event
{
    public function __construct(
        private int $writer_id,
        private int $task_id,
        private DateTimeImmutable $time
    ) {
    }

    public function getWriterId(): int
    {
        return $this->writer_id;
    }

    public function getTaskId(): int
    {
        return $this->task_id;
    }

    public function getTime(): DateTimeImmutable
    {
        return $this->time;
    }
}
