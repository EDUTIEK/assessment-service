<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Format;

use Edutiek\AssessmentService\System\Format\FullService as SystemFormat;
use Edutiek\AssessmentService\System\User\ReadService as SystemUser;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\ResultAvailableType;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as GradingService;
use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;
use Edutiek\AssessmentService\Assessment\Data\WritingStatus;

readonly class Service implements FullService
{
    public function __construct(
        private Language $language,
        private SystemUser $system_user,
        private SystemFormat $system_format,
        private GradingService $grading,
        private OrgaSettings $orga_settings,
    ) {
    }

    public function resultAvailability(): string
    {
        $txt = $this->language->txt(...);
        return match ($this->orga_settings->getResultAvailableType()) {
            ResultAvailableType::FINALISED => $txt('result_available_finalised'),
            ResultAvailableType::REVIEW => $txt('result_available_review'),
            ResultAvailableType::DATE => $this->system_format->dateRange($this->orga_settings->getResultAvailableDate(), null),
        };
    }

    public function finalResult(?Writer $writer): string
    {
        if (null === $writer) {
            return $this->language->txt('result_not_available');
        }

        if (null === $writer->getCorrectionFinalized()) {
            return $this->language->txt('result_not_finalized');
        }

        $level = $this->grading->getGradLevelForPoints($writer->getFinalPoints());
        if (null === $level) {
            $text = $this->language->txt('result_not_graded');
        } else {
            $text = $level->getGrade();
        }

        if ($writer->getFinalPoints()) {
            $text .= ' (' . $writer->getFinalPoints() . ' ' . $this->language->txt('points') . ')';
        }

        if ($writer->getStitchComment()) {
            $text .= ' ' . $this->language->txt('via_stitch_decision');
        }

        return $text;
    }

    public function finalizedFromStatus(Writer $writer): ?string
    {
        $txt = $this->language->txt(...);

        return match($writer->getFinalizedFromStatus()) {
            CorrectionStatus::APPROXIMATION => $txt("via_approximation"),
            CorrectionStatus::CONSULTING => $txt("via_consulting"),
            CorrectionStatus::STITCH => $txt("via_stitch_decision"),
            default => null
        };
    }

    public function writingStatus(Writer $writer): ?string
    {
        $txt = $this->language->txt(...);

        return match($writer->getWritingStatus()) {
            WritingStatus::NOT_STARTED => $txt("writing_status_not_started"),
            WritingStatus::STARTED => $txt("writing_status_started"),
            WritingStatus::EXCLUDED => $txt("writing_status_excluded_by") . " " .
                $this->system_user->getUser($writer->getWritingExcludedBy() ?? 0)?->getFullname(false)
                ?? $txt('unknown'),
            WritingStatus::AUTHORIZED =>
            ($writer->getUserId() === $writer->getWritingAuthorizedBy()
                ? $txt("writing_status_authorized")
                : $txt("writing_status_authorized_by") . " " .
                    $this->system_user->getUser($writer->getWritingAuthorizedBy() ?? 0)?->getFullname(false)
                        ?? $txt('unknown'))
        };
    }

}
