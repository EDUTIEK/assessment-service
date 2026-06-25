<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use DateTimeImmutable;
use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event is raised when the authorization of a writing is removed
 *
 * - remove the pregrading of corrections
 * - set the correction process status to open
 *
 * Authorized corrections should not exist, because of the constraint contraint RemoveWritingAuthorization
 */
readonly class WritingAuthorizationRemoved implements Event
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
