<?php

namespace Edutiek\AssessmentService\Task\Format;

use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as GradingService;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Assessment\Data\CorrectionProcedure;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;

readonly class Service implements FullService
{
    public function __construct(
        private LanguageService $lang,
        private GradingService $grade_level_service,
        private CorrectionSettings $settings
    ) {
    }

    public function correctionResult(?CorrectorSummary $summary, bool $is_own): string
    {
        $status = $this->gradingStatus($summary?->getGradingStatus(), $is_own);

        $grade = null;
        $points = null;

        if ($is_own || $summary?->isAuthorized()) {
            $points = $summary?->getEffectivePoints();
            if ($points !== null) {
                $grade = $this->grade_level_service->getGradLevelForPoints($points)?->getGrade();
                $points = $points . ' ' . $this->lang->txt($points == 1 ? 'point' : 'points');
            }
        }

        if ($grade !== null) {
            return "$status - $grade ($points)";
        } elseif ($points !== null) {
            return "$status - $points";
        } else {
            return "$status";
        }
    }

    public function gradingStatus(?GradingStatus $status, $is_own): string
    {
        $instant = $is_own || $this->settings->getInstantStatus();

        return match($status) {
            GradingStatus::PRE_GRADED => $instant
                ? $this->lang->txt('grading_pre_graded')
                : $this->lang->txt('grading_open'),
            GradingStatus::OPEN => $this->lang->txt('grading_open'),
            GradingStatus::AUTHORIZED => $this->lang->txt('grading_authorized'),
            GradingStatus::REVISED => match ($this->settings->getProcedure()) {
                CorrectionProcedure::APPROXIMATION => $this->lang->txt('grading_approximated'),
                CorrectionProcedure::CONSULTING => $this->lang->txt('grading_consulted'),
                CorrectionProcedure::NONE => $this->lang->txt('grading_revised'),
            },
            default => $instant
                ? $this->lang->txt('grading_not_started')
                : $this->lang->txt('grading_open'),
        };
    }

    public function gradingStatusOptions(): array
    {
        return [
            GradingStatus::NOT_STARTED->value => $this->lang->txt('grading_not_started'),
            GradingStatus::OPEN->value => $this->lang->txt('grading_open'),
            GradingStatus::PRE_GRADED->value => $this->lang->txt('grading_pre_graded'),
            GradingStatus::AUTHORIZED->value => $this->lang->txt('grading_authorized'),
            GradingStatus::REVISED->value => match($this->settings->getProcedure()) {
                CorrectionProcedure::APPROXIMATION => $this->lang->txt('grading_approximated'),
                CorrectionProcedure::CONSULTING => $this->lang->txt('grading_consulted'),
                CorrectionProcedure::NONE => $this->lang->txt('grading_revised'),
            },
        ];
    }

    public function gradingPositionOptions(): array
    {
        return [
            (string) GradingPosition::FIRST->value => $this->lang->txt('grading_pos_first'),
            (string) GradingPosition::SECOND->value => $this->lang->txt('grading_pos_second'),
            (string) GradingPosition::STITCH->value => $this->lang->txt('grading_pos_stitch'),
        ];
    }
}
