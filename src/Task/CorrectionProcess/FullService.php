<?php

namespace Edutiek\AssessmentService\Task\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use ILIAS\Plugin\LongEssayAssessment\Task\Data\CorrectorAssignment;

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
    public function canAuthorize(CorrectorAssignment $assignment): bool;

    /**
     * Check if the correction of an assigned task can be revised
     */
    public function canRevise(CorrectorAssignment $assignment): bool;

    /**
     * Authorize a correction as a corrector
     */
    public function authorizeCorrection(CorrectorAssignment $assignment): Result;

    /**
     * Remove all correction authorizations of a writer
     * - This must be called by an admin, not by a corrector
     */
    public function removeAuthorizations(int $task_id, Writer $writer): Result;

    /**
     * Check a summary and save it it possible as a corrector
     * - Check the process conditions and if the summary content is able to be authorized
     * - Trigger further actions if status is changed
     */
    public function checkAndSaveSummary(CorrectorSummary $summary): Result;
}
