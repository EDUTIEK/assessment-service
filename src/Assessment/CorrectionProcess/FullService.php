<?php

namespace Edutiek\AssessmentService\Assessment\CorrectionProcess;

use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService
{
    public function removeFinalization(Writer $writer);
}
