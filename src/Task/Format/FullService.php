<?php

namespace Edutiek\AssessmentService\Task\Format;

use Edutiek\AssessmentService\Task\Data\CorrectorSummary;

interface FullService
{
    public function correctionResult(?CorrectorSummary $summary, bool $onlyStatus = false, $onlyAuthorizedGrades = false) : string;

}