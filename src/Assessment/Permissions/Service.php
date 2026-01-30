<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Permissions;

use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\ParticipationType;
use Edutiek\AssessmentService\Assessment\Data\Permissions;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Data\ResultAvailableType;
use Edutiek\AssessmentService\Assessment\WorkingTime\Factory as WorkingTimeFactory;
use Edutiek\AssessmentService\Assessment\Data\Writer;

class Service implements ReadService
{
    private Permissions $permissions;
    private ?OrgaSettings $orga_settings;
    private ?CorrectionSettings $correction_settings;

    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly int $user_id,
        private readonly Repositories $repos,
        private readonly WorkingTimeFactory $working_time_factory
    ) {
        $this->permissions = $this->repos->permissions()->one($this->ass_id, $this->context_id, $this->user_id);
        $this->orga_settings = $this->repos->orgaSettings()->one(($this->ass_id)) ?? $this->repos->orgaSettings()->new();
        $this->correction_settings = $this->repos->correctionSettings()->one($this->ass_id) ?? $this->repos->correctionSettings()->new();
    }

    public function canViewInfoScreen(): bool
    {
        return $this->permissions->getMaintainSettings();
    }

    public function canViewWriterScreen(): bool
    {
        return $this->permissions->getRead() &&
            ($this->isOnline() || $this->canEditOrgaSettings()) &&
            ($this->orga_settings->getParticipationType() === ParticipationType::INSTANT || $this->isWriter() || $this->canEditOrgaSettings());
    }

    public function canViewCorrectorScreen(): bool
    {
        return $this->permissions->getRead() &&
            ($this->isOnline() || $this->canEditOrgaSettings()) &&
            $this->isCorrector();
    }

    public function canEditOrgaSettings(): bool
    {
        return $this->permissions->getMaintainSettings();
    }

    public function canEditTechnicalSettings(): bool
    {
        return $this->permissions->getMaintainSettings();
    }

    public function canEditContentSettings(): bool
    {
        return $this->permissions->getMaintainContent();
    }

    public function canEditGrades(): bool
    {
        return $this->permissions->getMaintainContent();
    }

    public function canMaintainWriters(): bool
    {
        return $this->permissions->getMaintainWriting();
    }

    public function canMaintainCorrectors(): bool
    {
        return $this->permissions->getMaintainCorrection();
    }

    public function canExportObject(): bool
    {
        return $this->canEditOrgaSettings() && $this->canEditContentSettings() && $this->canEditTechnicalSettings();

    }

    public function canWrite(): bool
    {
        if (!$this->canViewWriterScreen()) {
            return false;
        }

        $writer = $this->getWriter();
        if ($writer === null || $writer->getWritingAuthorized() !== null || $writer->getWritingExcluded() !== null) {
            return false;
        }

        $working_time = $this->working_time_factory->workingTime($this->orga_settings, $writer);
        return $working_time->isNowInAllowedTime();
    }

    public function canViewSolution(): bool
    {
        if (!$this->canViewWriterScreen() || !$this->orga_settings->getSolutionAvailable()) {
            return false;
        }

        return $this->orga_settings->getSolutionAvailableDate() === null ||
            $this->orga_settings->getSolutionAvailableDate()->getTimestamp() <= time();
    }

    public function canViewWriterStatistics(): bool
    {
        return true;
        return $this->canViewResult() && $this->orga_settings->getStatisticsAvailable();
    }

    /**
     *  Check if the current user can view the statistics
     */
    public function canViewCorrectionStatistics(): bool
    {
        return ($this->canCorrect() || $this->canMaintainCorrectors()) && !$this->orga_settings->getMultiTasks();
    }

    public function canViewResult(): bool
    {
        if (!$this->canViewWriterScreen() || $this->getWriter()?->getCorrectionFinalizedBy() === null) {
            return false;
        }

        switch ($this->orga_settings->getResultAvailableType()) {
            case ResultAvailableType::FINALISED:
                return true;
            case ResultAvailableType::REVIEW:
                return $this->canReviewCorrectedAssessment();
            case ResultAvailableType::DATE:
                return $this->orga_settings->getResultAvailableDate() === null ||
                    $this->orga_settings->getResultAvailableDate()->getTimestamp() >= time();
        }
        return false;

    }

    public function canReviewWrittenAssessment(): bool
    {
        if (!$this->canViewWriterScreen()) {
            return false;
        }

        if ($this->canWrite()) {
            // no review if writing is (still) possible
            return false;
        }

        if (!$this->orga_settings->getKeepAvailable()) {
            return false;
        }

        return $this->getWriter()?->getWorkingStart() !== null;
    }

    public function canReviewCorrectedAssessment(): bool
    {
        if (!$this->canViewWriterScreen() || !$this->orga_settings->getReviewEnabled()) {
            return false;
        }

        if ($this->orga_settings->getReviewStart() !== null &&
            $this->orga_settings->getReviewStart()->getTimestamp() < time()) {
            return false;
        }
        if ($this->orga_settings->getReviewEnd() !== null &&
            $this->orga_settings->getReviewStart()->getTimestamp() > time()) {
            return false;
        }

        return $this->getWriter()?->getCorrectionFinalizedBy() !== null;
    }

    public function canCorrect(): bool
    {
        if (!$this->canViewCorrectorScreen()) {
            return false;
        }

        if ($this->orga_settings->getCorrectionStart() !== null &&
            $this->orga_settings->getCorrectionStart()->getTimestamp() > time()) {
            return false;
        }
        if ($this->orga_settings->getCorrectionEnd() !== null &&
            $this->orga_settings->getCorrectionEnd()->getTimestamp() < time()) {
            return false;
        }

        return true;

    }

    public function canWriteCorrectionReport(): bool
    {
        if (!$this->canViewCorrectorScreen() || !$this->correction_settings->getReportsEnabled()) {
            return false;
        }

        if ($this->orga_settings->getCorrectionStart() !== null &&
            $this->orga_settings->getCorrectionStart()->getTimestamp() > time()) {
            return false;
        }
        if ($this->orga_settings->getCorrectionEnd() !== null &&
            $this->orga_settings->getCorrectionEnd()->getTimestamp() < time()) {
            return false;
        }

        return true;

    }

    public function canDownloadCorrectionReports(): bool
    {
        if (!$this->canViewWriterScreen() || !$this->correction_settings->getReportsEnabled()) {
            return false;
        }

        return $this->correction_settings->getReportsAvailableStart()?->getTimestamp() >= time();
    }

    public function canDoRestCall(): bool
    {
        return $this->permissions->getRead() && $this->isOnline() || $this->canEditContentSettings();
    }

    public function canUploadFiles(): bool
    {
        return $this->canEditContentSettings() || $this->canMaintainWriters()
            || $this->isWriter() && $this->isOnline()
            || $this->isCorrector() && $this->isOnline();
    }

    public function canEditTemplates(): bool
    {
        return $this->permissions->getEditTemplates();
    }

    private function isOnline(): bool
    {
        return (bool) $this->repos->orgaSettings()->one($this->ass_id)?->getOnline();
    }

    private function isWriter(): bool
    {
        return $this->getWriter() !== null;
    }

    private function isCorrector(): bool
    {
        return $this->repos->corrector()->oneByUserIdAndAssId($this->user_id, $this->ass_id) !== null;
    }

    private function getWriter(): ?Writer
    {
        return $this->repos->writer()->oneByUserIdAndAssId($this->user_id, $this->ass_id);
    }

    public function canEditDocumentationSettings()
    {
        return $this->permissions->getMaintainSettings();
    }

    public function canViewDashboard(): bool
    {
        return $this->permissions->getProctorWriting();
    }

}
