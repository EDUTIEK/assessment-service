<?php

namespace Edutiek\AssessmentService\Task\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\WritingTask;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;

interface FullService
{
    /**
     * Check if an assigned writing can be corrected by the assigned corrector
     * - Writing must be authorized
     * - Status must be stitch for stich decider (third corrector)
     * - Own status must be not started or open for normal corrector
     * - Check if second corrector has to wait for the first corrector
     * - Authorization is taken into account here, but not the pre-graded status
     * - The pre-graded status is handled in the corrector app
     */
    public function canCorrect(CorrectorAssignment $assignment): bool;

    /**
     * Check if an assigned correction can be authorized by the assigned corrector
     * - Check the process conditions and status but not the summary content
     */
    public function canAuthorizeOwnCorrection(CorrectorAssignment $assignment): bool;

    /**
     * Check if the authorization of a correction can be removed by an assigned corrector
     * - Check the process conditions and status
     */
    public function canRemoveOwnAuthorization(CorrectorAssignment $assignment): bool;

    /**
     * Check if a second corrector can remove the authorization of the first corrector
     * - Check the process conditions and status
     */
    public function canRemoveFirstAuthorization(CorrectorAssignment $assignment): bool;

    /**
     * Check if the correction of an assigned task can be revised
     */
    public function canRevise(CorrectorAssignment $assignment): bool;

    /**
     * Authorize a correction as a corrector
     */
    public function authorizeOwnCorrection(CorrectorAssignment $assignment): Result;

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
