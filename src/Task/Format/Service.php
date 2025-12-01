<?php

namespace Edutiek\AssessmentService\Task\Format;

use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as GradingService;

class Service implements FullService
{
    public function __construct(
        private LanguageService $language,
        private GradingService $grade_level_service
    ){}

    public function correctionResult(
        ?CorrectorSummary $summary,
        bool $onlyStatus = false,
        $onlyAuthorizedGrades = false
    ): string {
        if (empty($summary) || empty($summary->getLastChange())) {
            return $this->language->txt('grading_not_started');
        }
        // todo: improve
        if (!$summary->isAuthorized()) {
            $onlyStatus = true;
        }

        $grade = function ($text) use ($summary) {
            $grade = null;
            $points = null;

            if ($level = $this->grade_level_service->getGradLevelForPoints($summary->getPoints())) {
                $grade = $level->getGrade();
            }
            if (!empty($summary->getPoints())) {
                $points = ($grade ? " (": "(") . $summary->getPoints() . ' ' . $this->language->txt('points') . ')';
            }
            return ($grade||$points) ? "$text - $grade$points" :  $text;
        };

        if (empty($summary->getCorrectionAuthorized())) {
            $text = $this->language->txt('grading_open');

            if($onlyStatus || $onlyAuthorizedGrades) {
                return  $text;
            }

            return $grade($text);
        }

        $text = $this->language->txt('grading_authorized');

        if ($onlyStatus) {
            return $text;
        }

        return $grade($text);
    }
}