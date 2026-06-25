<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling\Actions;

use Edutiek\AssessmentService\System\ConstraintHandling\Action;

/**
 * This action must be checked before the content provided by a writer is changed
 *
 * - BLOCK if the writing is authorized
 *
 * All correction activity is based on authorized writings.
 * If an admin wants to change writing content, the authorization must be removed first.
 *
 * @param bool $as_admin
 */
readonly class ChangeWritingContent implements Action
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
