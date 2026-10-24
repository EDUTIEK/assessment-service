<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling\Actions;

use Edutiek\AssessmentService\System\ConstraintHandling\Action;

/**
 * This action must be checked before the status of a writing is changed
 * - Remove writing authorization
 * - Remove writer
 * - Convert text to PDF
 *
 * Result is:
 * - BLOCK if a correction is authorized
 * - ASK if correction data exists
 */
readonly class ChangeWritingStatus implements Action
{
    public function __construct(
        private int $writer_id
    ) {
    }

    public function getWriterId(): int
    {
        return $this->writer_id;
    }
}
