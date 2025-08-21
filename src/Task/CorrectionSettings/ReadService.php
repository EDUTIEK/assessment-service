<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Task\CorrectionSettings;

use Edutiek\AssessmentService\Task\Data\CorrectionSettings;
use Edutiek\AssessmentService\Task\Data\CriteriaMode;

interface ReadService
{
    public function get(): CorrectionSettings;
}