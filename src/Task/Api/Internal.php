<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use Edutiek\AssessmentService\Task\AppBridges\WriterBridge as WriterBridgeService;
use Edutiek\AssessmentService\Task\AppBridges\CorrectorBridge as CorrectorBridgeService;
use Edutiek\AssessmentService\Task\AssessmentStatus\Service as StatusService;
use Edutiek\AssessmentService\Task\Checks\Service as ChecksService;
use Edutiek\AssessmentService\Task\ConstraintHandling\Provider as ConstraintProvider;
use Edutiek\AssessmentService\Task\CorrectionProcess\Service as CorrectionProcessService;
use Edutiek\AssessmentService\Task\CorrectionSettings\Service as CorrectionSettingsService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\Service as CorrectorAssignmentsService;
use Edutiek\AssessmentService\Task\CorrectorComment\Service as CorrectorCommentService;
use Edutiek\AssessmentService\Task\CorrectorSummary\Service as CorrectorSummaryService;
use Edutiek\AssessmentService\Task\CorrectorTemplate\Service as CorrectorTemplateService;
use Edutiek\AssessmentService\Task\EventHandling\Observer as EventObserver;
use Edutiek\AssessmentService\Task\Format\Service as FormatService;
use Edutiek\AssessmentService\Task\Manager\Service as ManagerService;
use Edutiek\AssessmentService\Task\RatingCriterion\Service as RatingCriterionService;
use Edutiek\AssessmentService\Task\Resource\Service as ResourceService;
use Edutiek\AssessmentService\Task\Settings\Service as SettingsService;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Task\PdfCreation\CorrectionProvider;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ExcelAssignmentData;

class Internal
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    public function format(int $ass_id, int $user_id): FormatService
    {
        return $this->instances[FormatService::class][$ass_id][$user_id] ??= new FormatService(
            $this->language($user_id),
            $this->dependencies->assessmentApi($ass_id, $user_id)->assessmentGrading()
        );
    }

    /**
     * Translation of language variables
     */
    public function language($user_id): LanguageService
    {
        return $this->dependencies->systemApi()->language($user_id, __DIR__ . '/../Languages/');
    }

    /**
     * Helper service for scope checks
     */
    public function checks(int $ass_id, int $user_id): ChecksService
    {
        return $this->instances[ChecksService::class][$ass_id][$user_id] ??= new ChecksService(
            $ass_id,
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->corrector(),
            $this->dependencies->repositories()
        );
    }

    public function constraintProvider(int $ass_id, int $user_id): ConstraintProvider
    {
        return $this->instances[ConstraintProvider::class][$ass_id][$user_id] ??= new ConstraintProvider(
            $ass_id,
            $user_id,
            $this
        );
    }

    public function correctorAssignments(int $ass_id, int $user_id): CorrectorAssignmentsService
    {
        return $this->instances[CorrectorAssignmentsService::class][$ass_id][$user_id] ??= new CorrectorAssignmentsService(
            $ass_id,
            $user_id,
            $this->dependencies->assessmentApi($ass_id, $user_id)->correctionSettings()->get(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->corrector(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->systemApi()->spreadsheet(true),
            $this->dependencies->systemApi()->language($user_id, __DIR__ . '/../Languages/'),
            $this->dependencies->systemApi()->fileDelivery(),
            $this->dependencies->systemApi()->fileStorage(),
            $this,
            $this->dependencies->repositories(),
            $this->dependencies->eventDispatcher($ass_id, $user_id)
        );
    }

    public function excelAssignmentData(int $ass_id, int $user_id): ExcelAssignmentData
    {
        return $this->instances[ExcelAssignmentData::class][$ass_id][$user_id] ??= new ExcelAssignmentData(
            $this->dependencies->assessmentApi($ass_id, $user_id)->correctionSettings()->get(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->orgaSettings()->get(),
            $this->dependencies->repositories()->settings()->allByAssId($ass_id),
            $this->dependencies->assessmentApi($ass_id, $user_id)->corrector(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->correctorAssignments($ass_id, $user_id),
            $this->dependencies->assessmentApi($ass_id, $user_id)->location(),
            $this->dependencies->systemApi()->user(),
            $this->language($user_id)
        );
    }

    public function correctionProcess(int $ass_id, int $user_id): CorrectionProcessService
    {
        return $this->instances[CorrectionProcessService::class][$ass_id][$user_id] ??= new CorrectionProcessService(
            $user_id,
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->correctionProcess(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->logEntry(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->correctionSettings()->get(),
            $this->correctorSummary($ass_id, $user_id),
            $this->language($user_id),
        );
    }

    public function correctorBridge(int $ass_id, int $user_id): CorrectorBridgeService
    {
        return $this->instances[CorrectorBridgeService::class][$ass_id][$user_id] ??= new CorrectorBridgeService(
            $ass_id,
            $user_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->systemApi()->entity(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->corrector(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->correctionSettings(),
            $this->correctionSettings($ass_id, $user_id),
            $this->correctorAssignments($ass_id, $user_id),
            $this->correctorSummary($ass_id, $user_id),
            $this->correctorTemplate($ass_id, $user_id),
            $this->correctionProcess($ass_id, $user_id),
            $this->language($user_id),
            $this->dependencies->systemApi()->user()
        );
    }

    public function correctorComment(int $ass_id, int $user_id): CorrectorCommentService
    {
        return $this->instances[CorrectorCommentService::class][$ass_id][$user_id] = new CorrectorCommentService(
            $ass_id,
            $user_id,
            $this->dependencies->repositories(),
            $this->language($user_id),
        );
    }

    public function correctorSummary(int $ass_id, int $user_id): CorrectorSummaryService
    {
        return $this->instances[CorrectorSummaryService::class][$ass_id][$user_id] ??= new CorrectorSummaryService(
            $this->checks($ass_id, $user_id),
            $this->dependencies->repositories()
        );
    }

    public function correctorTemplate(int $ass_id, int $user_id): CorrectorTemplateService
    {
        return $this->instances[CorrectorTemplateService::class][$ass_id][$user_id] ??= new CorrectorTemplateService(
            $this->checks($ass_id, $user_id),
            $this->dependencies->repositories()
        );
    }

    public function correctionPartProvider(int $ass_id, int $user_id): ?PdfPartProvider
    {
        return $this->instances[CorrectionProvider::class][$ass_id][$user_id] ?? new CorrectionProvider(
            $ass_id,
            $user_id,
            $this->dependencies->systemApi()->htmlProcessing(),
            $this->dependencies->systemApi()->pdfProcessing(),
            $this->language($user_id),
            $this->dependencies->assessmentApi($ass_id, $user_id)->correctionSettings(),
            $this->correctorAssignments($ass_id, $user_id),
            $this->correctorSummary($ass_id, $user_id),
            $this->dependencies->assessmentApi($ass_id, $user_id)->corrector(),
        );
    }

    public function correctionSettings(int $ass_id, int $user_id): CorrectionSettingsService
    {
        return $this->instances[CorrectionSettingsService::class][$ass_id][$user_id] = new CorrectionSettingsService(
            $ass_id,
            $this->dependencies->repositories(),
            $this->correctorAssignments($ass_id, $user_id),
            $this->assessmentStatus($ass_id, $user_id),
            $this->language($user_id),
        );
    }

    public function assessmentStatus(int $ass_id, int $user_id): StatusService
    {
        return $this->instances[StatusService::class][$ass_id][$user_id] = new StatusService(
            $ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->correctorAssignments($ass_id, $user_id),
        );
    }

    public function eventObserver(int $ass_id, int $user_id): EventObserver
    {
        return $this->instances[EventObserver::class][$ass_id][$user_id] ??= new EventObserver(
            $ass_id,
            $user_id,
            $this,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
        );
    }

    public function ratingCriterion(int $task_id): RatingCriterionService
    {
        return $this->instances[RatingCriterionService::class][$task_id] ??= new RatingCriterionService(
            $task_id,
            $this->dependencies->repositories()
        );
    }

    public function resource(int $task_id): ResourceService
    {
        return $this->instances[ResourceService::class][$task_id] ??= new ResourceService(
            $task_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
        );
    }

    public function settings(int $ass_id, int $task_id): SettingsService
    {
        return $this->instances[SettingsService::class][$ass_id][$task_id] ??= new SettingsService(
            $ass_id,
            $task_id,
            $this->dependencies->repositories()
        );
    }

    public function taskManager(int $ass_id, int $user_id): ManagerService
    {
        return $this->instances[ManagerService::class][$ass_id][$user_id] ??= new ManagerService(
            $ass_id,
            $user_id,
            $this->dependencies->repositories(),
            $this->correctionSettings($ass_id, $user_id),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->typeApis(),
            $this->language($user_id),
        );
    }

    public function writerBridge(int $ass_id, int $user_id): WriterBridgeService
    {
        return $this->instances[WriterBridgeService::class][$ass_id][$user_id] ??= new WriterBridgeService(
            $ass_id,
            $user_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->systemApi()->entity(),
            $this->dependencies->systemApi()->htmlProcessing(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->language($user_id),
        );
    }
}
