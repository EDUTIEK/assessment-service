<?php

namespace Edutiek\AssessmentService\Task\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\WritingTask;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;

interface FullService extends ReadService
{
    /**
     * Authorize a correction as a corrector
     */
    public function authorizeOwnCorrection(CorrectorAssignment $assignment, bool $in_backend = false): Result;

    /**
     * Remove the authorization of an own correction
     */
    public function removeOwnAuthorization(CorrectorAssignment $assignment): Result;

    /**
     * Remove the authorization of a first corrector as a second corrector
     */
    public function removeFirstAuthorization(CorrectorAssignment $assignment, string $reason): Result;


    /**
     * Get a list process steps that can be removed by an administrator (in descending order)
     * @param WritingTask[] $writing_tasks
     * @return array<int, string> step value => step title (translated)
     */
    public function getRemovableStepsOptions(array $writing_tasks): array;

    /**
     * Remove authorization or revision steps that are equal or higher than the given step
     * - This must be called by an admin, not by a corrector
     */
    public function removeEqualOrHigherSteps(int $task_id, Writer $writer, ProcessStep $start_step, ?string $reason): Result;

    /**
     * Check a summary and save it it possible as a corrector
     * - Check the process conditions and if the summary content is able to be authorized
     * - Trigger further actions if status is changed
     */
    public function checkAndSaveSummary(CorrectorSummary $summary): Result;
}
