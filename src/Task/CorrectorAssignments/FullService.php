<?php

namespace Edutiek\AssessmentService\Task\CorrectorAssignments;

use Edutiek\AssessmentService\Task\Data\GradingStatus;

interface FullService extends ReadService
{
    /**
     * Save a filter for showing assignments
     * This is set on the start page of a corrector
     * This is used to filter the assigned items in the corrector app
     *
     * @param GradingStatus[]|null $grading_status
     */
    public function saveCorrectorFilter(int $corrector_id, ?array $grading_status, ?int $position);

}
