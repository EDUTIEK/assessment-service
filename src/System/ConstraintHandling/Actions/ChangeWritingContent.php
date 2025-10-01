<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling\Actions;

use Edutiek\AssessmentService\System\ConstraintHandling\Action;

/**
 * This action should be checked when the content provided by a writer is changed
 *
 * @param bool $as_admin
 */
readonly class ChangeWritingContent implements Action
{
    public function __construct(
        private int $writer_id,
        private int $task_id,
        private bool $as_admin
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

    /**
     * @return bool the action is performed by an admin
     */
    public function isAdmin(): bool
    {
        return $this->as_admin;
    }
}
