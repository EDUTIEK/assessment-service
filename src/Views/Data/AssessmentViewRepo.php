<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Views\Data;

interface AssessmentViewRepo
{
    /**
     * Get the assessments to which a corrector has access
     * @return CorrectorAssessmentView[]
     */
    public function correctorAssessments(int $user_id): array;
}
