<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Permissions;

interface ReadService
{
    /**
     * Check if a screen with information about the assessment can be viewed
     */
    public function canViewInfoScreen(): bool;

    /**
     * Check if the current user can view the writer screen
     * The screen is available until the end of the writing period or if the user is a writer
     */
    public function canViewWriterScreen(): bool;

    /**
     * Check if the current user can view the corrector screen
     */
    public function canViewCorrectorScreen(): bool;

    /**
     * Check if the current user can edit the organisational settings (online, dates)
     */
    public function canEditOrgaSettings(): bool;

    /**
     * Check if the current user can edit additional material
     */
    public function canEditTechnicalSettings(): bool;

    /**
     * Check if the current user can edit the content settings
     */
    public function canEditContentSettings(): bool;

    /**
     * Check if the current user can edit the grades
     */
    public function canEditGrades(): bool;

    /**
     * Check if the current user can maintain the writers
     */
    public function canMaintainWriters(): bool;

    /**
     * Check if the current user can maintain the writers
     */
    public function canMaintainCorrectors(): bool;

    /**
     * Check if the current user can export the assessment object
     */
    public function canExportObject(): bool;

    /**
     * Check if the current user can write the essay
     */
    public function canWrite(): bool;

    /**
     *  Check if the current user can view the solution
     */
    public function canViewSolution(): bool;

    /**
     *  Check if the current user can view the statistics
     */
    public function canViewWriterStatistics(): bool;

    /**
     * Check if the current user can view his assessment result
     */
    public function canViewResult(): bool;

    /**
     * Check if the current user can review his/her own assessment (authorized or not)
     */
    public function canReviewWrittenAssessment(): bool;

    /**
     * Check if the current user can review the correction of his/her own assessment
     */
    public function canReviewCorrectedAssessment(): bool;

    /**
     * Check if the current user can correct assessments
     */
    public function canCorrect(): bool;

    /**
     * Check if the current user can write a correction report
     */
    public function canWriteCorrectionReport(): bool;

    /**
     * Check if the current user can download a correction report
     */
    public function canDownloadCorrectionReports(): bool;

    /**
     * Check if the user can do a REST call
     */
    public function canDoRestCall(): bool;

    /**
     * Check if the user is allowed to upload files
     */
    public function canUploadFiles(): bool;

    /**
     * Check if the user can set the assessment as template and fix/unfix settings
     */
    public function canEditTemplates(): bool;
}
