<?php

namespace Edutiek\AssessmentService\Assessment\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;

interface FullService
{
    /**
     * Get the result status that would be set when a corrector authorizes his grading
     * This is used to to create a warning about the need for a procedure or stitch decision
     */
    public function getAuthorizationResultStatus(Writer $writer, int $task_id, int $corrector_id): CorrectionStatus;

    /**
     * Reset a writers status to something before finalisation
     */
    public function resetStatus(Writer $writer, CorrectionStatus $status);

    /**
     * Update the writer's correction status when a corrector summary is changed
     */
    public function updateStatus(Writer $writer): CorrectionStatus;
}
