<?php

namespace Edutiek\AssessmentService\Assessment\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService
{
    public function setCorrectionOpen(Writer $writer);

    /**
     * Update the writer's correction status when a corrector summary is changed
     */
    public function updateStatus(Writer $writer);
}
