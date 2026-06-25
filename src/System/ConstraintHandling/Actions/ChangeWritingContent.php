<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling\Actions;

use Edutiek\AssessmentService\System\ConstraintHandling\Action;

/**
 * This action must be checked when the content provided by a writer should be changed
 *
 * It must return a ResultStatus::BLOCK if the writing is authorized-
 * If an admin wants to change writing content, the authorization must be removed first-
 *
 * All correction activity is based on authorized content,
 * therefore nothing has to be checked for the correction
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
