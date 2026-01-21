<?php

namespace Edutiek\AssessmentService\Task\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\ConstraintHandling\Result;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use ILIAS\Plugin\LongEssayAssessment\Task\Data\CorrectorAssignment;

interface FullService
{
    /**
     * Check if the task of a writer can be corrected by the assigned corrector
     * - Writing must be authorized
     * - status must be stitch for stich decider (third corrector)
     * - own status must be not started or open for normal corrector
     * - check if second corrector has to wait for the first corrector
     */
    public function canCorrect(CorrectorAssignment $assignment): bool;

    /**
     * Check if the correction of an assigned task can be authorized
     */
    public function canAuthorize(CorrectorAssignment $assignment): bool;

    /**
     * Check if the correction of an assigned task can be revised
     */
    public function canRevise(CorrectorAssignment $assignment): bool;

    /**
     * Authorize a correction
     */
    public function authorizeCorrection(CorrectorAssignment $assignment, int $user_id): Result;

    /**
     * @param Writer $writer
     * @param int    $user_id User executing this operation
     * @return bool
     */
    public function removeAuthorizations(int $task_id, Writer $writer, int $user_id): Result;

    /**
     * @param Writer $writer
     * @param int    $user_id User executing this operation
     * @return bool
     */
    public function removeCorrectorAuthorizations(Corrector $corrector, int $user_id): bool;

    /**
     * Check a summary and save it it possible
     * Trigger further actions if status is changed
     * @param int $user_id User executing this operation
     */
    public function checkAndSaveSummary(CorrectorSummary $summary): Result;
}
