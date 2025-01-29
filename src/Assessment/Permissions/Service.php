<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Permissions;

use Edutiek\AssessmentService\Assessment\Data\ObjectRepo;
use Edutiek\AssessmentService\Assessment\Data\ObjectPermissions;

class Service implements ReadService
{
    private ObjectPermissions $object_permissions;

    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly int $user_id,
        private readonly ObjectRepo $object_repo,
    ) {
        $this->object_permissions = $this->object_repo->getObjectPermissions($this->ass_id, $this->context_id, $this->user_id);
    }

    public function canViewInfoScreen(): bool
    {
        // TODO: Implement canViewInfoScreen() method.
    }

    public function canViewWriterScreen(): bool
    {
        // TODO: Implement canViewWriterScreen() method.
    }

    public function canViewCorrectorScreen(): bool
    {
        // TODO: Implement canViewCorrectorScreen() method.
    }

    public function canEditOrgaSettings(): bool
    {
        // TODO: Implement canEditOrgaSettings() method.
    }

    public function canEditTechnicalSettings(): bool
    {
        // TODO: Implement canEditTechnicalSettings() method.
    }

    public function canEditContentSettings(): bool
    {
        // TODO: Implement canEditContentSettings() method.
    }

    public function canEditGrades(): bool
    {
        // TODO: Implement canEditGrades() method.
    }

    public function canMaintainWriters(): bool
    {
        // TODO: Implement canMaintainWriters() method.
    }

    public function canMaintainCorrectors(): bool
    {
        // TODO: Implement canMaintainCorrectors() method.
    }

    public function canExportObject(): bool
    {
        // TODO: Implement canExportObject() method.
    }

    public function canWrite(): bool
    {
        // TODO: Implement canWrite() method.
    }

    public function canViewSolution(): bool
    {
        // TODO: Implement canViewSolution() method.
    }

    public function canViewWriterStatistics(): bool
    {
        // TODO: Implement canViewWriterStatistics() method.
    }

    public function canViewResult(): bool
    {
        // TODO: Implement canViewResult() method.
    }

    public function canReviewWrittenAssessment(): bool
    {
        // TODO: Implement canReviewWrittenAssessment() method.
    }

    public function canReviewCorrectedAssessment(): bool
    {
        // TODO: Implement canReviewCorrectedAssessment() method.
    }

    public function canCorrect(): bool
    {
        // TODO: Implement canCorrect() method.
    }

    public function canWriteCorrectionReport(): bool
    {
        // TODO: Implement canWriteCorrectionReport() method.
    }

    public function canDownloadCorrectionReports(): bool
    {
        // TODO: Implement canDownloadCorrectionReports() method.
    }
}