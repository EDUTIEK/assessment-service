<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as CorrectorAssignmentsFullService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\Service as CorrectorAssignmentsService;
use Edutiek\AssessmentService\Task\CorrectionSettings\Service as CorrectionSettingsService;
use Edutiek\AssessmentService\Task\CorrectionSettings\FullService as CorrectionSettingsFullService;
use Edutiek\AssessmentService\Task\AssessmentStatus\Service as StatusService;
use Edutiek\AssessmentService\Task\AssessmentStatus\FullService as StatusFullService;
use Edutiek\AssessmentService\Task\CorrectorComment\FullService as CorrectorCommentFullService;
use Edutiek\AssessmentService\Task\CorrectorComment\Service as CorrectorCommentService;
use Edutiek\AssessmentService\Task\CorrectorSummary\FullService as CorrectorSummaryFullService;
use Edutiek\AssessmentService\Task\CorrectorSummary\Service as CorrectorSummaryService;

class Internal
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    /**
     * Translation of language variables
     */
    public function language(string $code): LanguageService
    {
        return $this->instances[LanguageService::class][$code] ??= $this->dependencies->systemApi()->language()
            ->addLanguage('de', require(__DIR__ . '/../Languages/de.php'))
            ->setLanguage($code);
    }


    public function correctorAssignments(int $ass_id, int $user_id): CorrectorAssignmentsFullService
    {
        return $this->instances[CorrectorAssignmentsService::class][$ass_id] ??= new CorrectorAssignmentsService(
            $ass_id,
            $this->dependencies->assessmentApi($ass_id, $user_id)->correctionSettings()->get(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->repositories()
        );
    }

    public function correctorComment(int $task_id, int $writer_id): CorrectorCommentFullService
    {
        return $this->instances[CorrectorCommentService::class][$task_id][$writer_id] = new CorrectorCommentService(
            $task_id,
            $writer_id,
            $this->dependencies->repositories()
        );
    }

    public function correctorSummary(int $task_id): CorrectorSummaryFullService
    {
        return $this->instances[CorrectorSummaryService::class] ??= new CorrectorSummaryService(
            $task_id,
            $this->dependencies->repositories()
        );
    }

    public function correctionSettings(int $ass_id, int $user_id): CorrectionSettingsFullService
    {
        return $this->instances[CorrectionSettingsService::class] = new CorrectionSettingsService(
            $ass_id,
            $this->dependencies->repositories(),
            $this->correctorAssignments($ass_id, $user_id),
            $this->assessmentStatus($ass_id, $user_id)
        );
    }

    public function assessmentStatus(int $ass_id, int $user_id): StatusFullService
    {
        return $this->instances[StatusService::class] = new StatusService(
            $ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->correctorAssignments($ass_id, $user_id),
        );
    }
}
