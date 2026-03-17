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
use Edutiek\AssessmentService\Assessment\Data\CombinedStatus;

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

        $from = $this->finalizedFromStatus($writer);
        if ($from !== null) {
            $text .= ' ' . $from;
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

    public function writingStatus(Writer $writer): string
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

    public function writingStatusOptions(): array
    {
        $txt = $this->language->txt(...);

        return [
            WritingStatus::NOT_STARTED->value => $txt("writing_status_not_started"),
            WritingStatus::STARTED->value => $txt("writing_status_started"),
            WritingStatus::EXCLUDED->value => $txt("writing_status_excluded"),
            WritingStatus::AUTHORIZED->value => $txt("writing_status_authorized")
        ];
    }

    public function combinedStatus(Writer $writer): ?string
    {
        $txt = $this->language->txt(...);

        return match ($writer->getCombinedStatus()) {
            CombinedStatus::WRITING_EXCLUDED => $txt('combined_status_writing_excluded'),
            CombinedStatus::WRITING_NOT_STARTED => $txt('combined_status_writing_not_started'),
            CombinedStatus::WRITING_STARTED => $txt('combined_status_writing_started'),
            CombinedStatus::WRITING_AUTHORIZED => $txt('combined_status_open'), // no extra status
            CombinedStatus::OPEN => $txt('combined_status_open'),
            CombinedStatus::STITCH_NEEDED => $txt('combined_status_stitch'),
            CombinedStatus::FINALIZED => $txt('combined_status_finalized'),
            CombinedStatus::APPROXIMATION => $txt('combined_status_approximation'),
            CombinedStatus::CONSULTING => $txt('combined_status_consulting'),
        };
    }

    public function combinedStatusOptions(): array
    {
        $txt = $this->language->txt(...);

        return [
            CombinedStatus::WRITING_EXCLUDED->value => $txt('combined_status_writing_excluded'),
            CombinedStatus::WRITING_NOT_STARTED->value => $txt('combined_status_writing_not_started'),
            CombinedStatus::WRITING_STARTED->value => $txt('combined_status_writing_started'),
            CombinedStatus::OPEN->value => $txt('combined_status_open'),
            CombinedStatus::STITCH_NEEDED->value => $txt('combined_status_stitch'),
            CombinedStatus::FINALIZED->value => $txt('combined_status_finalized'),
            CombinedStatus::APPROXIMATION->value => $txt('combined_status_approximation'),
            CombinedStatus::CONSULTING->value => $txt('combined_status_consulting'),
        ];
    }

}
