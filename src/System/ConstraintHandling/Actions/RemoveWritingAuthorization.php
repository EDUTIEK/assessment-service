<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling\Actions;

use Edutiek\AssessmentService\System\ConstraintHandling\Action;

/**
 * This action must be checked when the authorization of a writing is removed
 *
 * - BLOCK if authorized corrections exist
 * - BLOCK if the correction process status is not open
 *
 * @param bool $as_admin
 */
readonly class RemoveWritingAuthorization implements Action
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
