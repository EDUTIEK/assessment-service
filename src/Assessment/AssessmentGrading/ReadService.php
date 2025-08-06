<?php

namespace Edutiek\AssessmentService\Assessment\AssessmentGrading;

use Edutiek\AssessmentService\Assessment\Data\GradeLevel;

interface ReadService
{
    /**
     * find the grade level by points
     * @param float|null $points
     * @return GradeLevel|null
     */
    public function getGradLevelForPoints(? float $points): ?GradeLevel;
    //

    /**
     * get the cached grade level for this assessment and id
     * @param int $id
     * @return GradeLevel|null
     */
    public function getGradeLevel(?int $id): ?GradeLevel;
}