<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSummary;

use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;

interface ReadService
{
    /** @return CorrectorSummary[] */
    public function allByTaskId(int $task_id): array;
    /** @return CorrectorSummary[] */
    public function allByTaskIdAndWriterId(int $task_id, int $writer_id): array;
    /** @return CorrectorSummary[] */
    public function allByTaskIdAndCorrectorId(int $task_id, int $corrector_id): array;

    /**
     * Get an existing summary or null
     */
    public function oneForAssignment(CorrectorAssignment $assignment): ?CorrectorSummary;

    /**
     * Get an existing summary or create one, but do not save it
     */
    public function getForAssignment(CorrectorAssignment $assignment): CorrectorSummary;

    /**
     * Get a new summry for an assignment, but do not save it
     */
    public function newForAssignment(CorrectorAssignment $assignment): CorrectorSummary;
}
