<?php

namespace Edutiek\AssessmentService\Assessment\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;

interface FullService
{
    /**
     * Reset a writers status to something before finalisation
     */
    public function resetStatus(Writer $writer, CorrectionStatus $status);

    /**
     * Update the writer's correction status when a corrector summary is changed
     */
    public function updateStatus(Writer $writer): CorrectionStatus;
}
