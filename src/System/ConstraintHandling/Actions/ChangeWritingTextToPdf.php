<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\ConstraintHandling\Actions;

use Edutiek\AssessmentService\System\ConstraintHandling\Action;

/**
 * This action must be checked before a writing text is changed to PDF
 *
 * - BLOCK if a correction is authorized
 * - ASK if correction data exists
 */
readonly class ChangeWritingTextToPdf implements Action
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
