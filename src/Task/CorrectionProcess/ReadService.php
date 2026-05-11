<?php

namespace Edutiek\AssessmentService\Task\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\WritingTask;
use Edutiek\AssessmentService\System\Data\Result;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;

interface ReadService
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
}
