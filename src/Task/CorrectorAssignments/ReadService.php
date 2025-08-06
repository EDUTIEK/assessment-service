<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;

interface ReadService
{
    /**
     * @return CorrectorAssignment[]
     */
    public function all(): array;
    /**
     * @return CorrectorAssignment[]
     */
    public function allByWriterId(int $writer_id): array;
    public function countMissingCorrectors();
}