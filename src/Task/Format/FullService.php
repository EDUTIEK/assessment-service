<?php

namespace Edutiek\AssessmentService\EssayTask\Format;

use Edutiek\AssessmentService\EssayTask\Data\CorrectorSummary;

interface FullService
{
//    public function correctionInclusions(
//        CorrectorSummary $summary,
//        CorrectorPreferences $preferences,
//        CorrectionSettings $settings,
//        bool $has_rating_criteria
//    ): string;
    public function correctionResult(?CorrectorSummary $summary, bool $onlyStatus = false, $onlyAuthorizedGrades = false) : string;

}