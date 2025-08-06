<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\AssessmentGrading;

use Edutiek\AssessmentService\Assessment\Data\GradeLevel;

interface FullService extends ReadService
{
    public function recalculate(): void;
}