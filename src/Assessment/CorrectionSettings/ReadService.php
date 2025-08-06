<?php

namespace Edutiek\AssessmentService\Assessment\CorrectionSettings;

use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;

interface ReadService
{
    public function get(): CorrectionSettings;
}