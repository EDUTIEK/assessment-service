<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

/**
 * Representation of a task to be done by a writer
 * Currently all task of an assesment need to be done by all writers
 * The assignment of a task to a writer is not yet an entity and it has no own id
 * This may change when other task types than the essay task are implemented
 */
readonly class WritingTask
{
    public function __construct(
        private int $writer_id,
        private int $task_id
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
}
