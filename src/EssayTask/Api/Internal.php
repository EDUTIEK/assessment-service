<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\EssayTask\AppBridges\CorrectorBridge as CorrectorBridgeService;
use Edutiek\AssessmentService\EssayTask\AppBridges\WriterBridge as WriterBridgeService;
use Edutiek\AssessmentService\EssayTask\AssessmentStatus\Service as StatusService;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\GenerateEssayImages;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\Service as BackgroundTaskService;
use Edutiek\AssessmentService\EssayTask\ConstraintHandling\Provider as ConstraintProvider;
use Edutiek\AssessmentService\EssayTask\Essay\Service as EssayService;
use Edutiek\AssessmentService\EssayTask\EssayImage\Service as EssayImageService;
use Edutiek\AssessmentService\EssayTask\EssayImport\ImportTypeBavaria;
use Edutiek\AssessmentService\EssayTask\EssayImport\ImportTypeNrw;
use Edutiek\AssessmentService\EssayTask\EssayImport\Service as ImportService;
use Edutiek\AssessmentService\EssayTask\EventHandling\Observer as EventObserver;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service as HtmlService;
use Edutiek\AssessmentService\EssayTask\ImageProcessing\Service as ImageService;
use Edutiek\AssessmentService\EssayTask\Manager\Service as ManagerService;
use Edutiek\AssessmentService\EssayTask\PdfCreation\CorrectionProvider;
use Edutiek\AssessmentService\EssayTask\PdfCreation\WritingProvider;
use Edutiek\AssessmentService\EssayTask\WritingSettings\Service as WritingSettingsService;
use Edutiek\AssessmentService\EssayTask\WritingSteps\Service as WritingStepsService;
use Edutiek\AssessmentService\System\BackgroundTask\Job;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;

class Internal
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    /**
     * Get the current service version
     * This is saved for written essays and relevant for their correction comments
     * By convention the version number is a coded date of the last relevant service change
     */
    public function serviceVersion(): int
    {
        return 20240603;
    }

    public function assessmentStatus(int $ass_id, int $user_id): StatusService
    {
        return $this->instances[StatusService::class][$ass_id][$user_id] = new StatusService(
            $this->dependencies->repositories(),
            $this->dependencies->taskApi($ass_id, $user_id)->tasks(),
        );
    }

    public function correctorBridge(int $ass_id, int $user_id): CorrectorBridgeService
    {
        return $this->instances[CorrectorBridgeService::class][$ass_id][$user_id] ??= new CorrectorBridgeService(
            $ass_id,
            $user_id,
            $this->dependencies->repositories(),
            $this->essayImage($ass_id, $user_id),
            $this->dependencies->systemApi()->entity(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->corrector(),
            $this->dependencies->taskApi($ass_id, $user_id)->correctorAssignments(),
            $this->dependencies->taskApi($ass_id, $user_id)->tasks(),
            $this->htmlProcessing($ass_id, $user_id)
        );
    }

    public function backgroundTask(int $ass_id, int $user_id, string $class): Job
    {
        $jobs = [
            GenerateEssayImages::class => fn() => $this->instances[GenerateEssayImages::class] ??= new GenerateEssayImages(
                $this->dependencies->repositories()->essay(),
                $this->essayImage($ass_id, $user_id),
            ),
        ];
        return $jobs[$class]();
    }

    public function backgroundTaskManager(int $ass_id, int $user_id): BackgroundTaskService
    {
        return $this->instances[BackgroundTaskService::class] ??= new BackgroundTaskService(
            $ass_id,
            $user_id,
            $this->dependencies->systemApi()->backgroundTask(),
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

    public function correctionPartProvider(int $ass_id, int $user_id): ?PdfPartProvider
    {
        return $this->instances[CorrectionProvider::class][$ass_id][$user_id] ?? new CorrectionProvider(
            $this->dependencies->repositories(),
            $this->htmlProcessing($ass_id, $user_id),
            $this->imageProcessing(),
            $this->dependencies->systemApi()->pdfProcessing(),
            $this->language($user_id),
            $this->dependencies->assessmentApi($ass_id, $user_id)->correctionSettings(),
            $this->dependencies->taskApi($ass_id, $user_id)->correctorAssignments(),
            $this->dependencies->taskApi($ass_id, $user_id)->correctorComments()
        );
    }

    public function essay(int $ass_id, int $user_id, bool $as_admin = false): EssayService
    {
        return $this->instances[EssayService::class] = new EssayService(
            $as_admin,
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->taskApi($ass_id, $user_id)->tasks(),
            $this->backgroundTaskManager($ass_id, $user_id),
            $this->essayImage($ass_id, $user_id),
            $this->language($user_id),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->eventDispatcher($ass_id, $user_id),
            $this->dependencies->constraintCollector($ass_id, $user_id),
            $this->writingPartProvider($ass_id, $user_id),
        );
    }

    public function essayImage(int $ass_id, int $user_id): EssayImageService
    {
        return $this->instances[EssayImageService::class] = new EssayImageService(
            $this->dependencies->repositories()->essayImage(),
            $this->dependencies->repositories()->essay(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->systemApi()->pdfConverter(),
            $this->writingPartProvider($ass_id, $user_id),
        );
    }

    public function eventObserver(int $ass_id, int $user_id): EventObserver
    {
        return $this->instances[EventObserver::class][$ass_id][$user_id] ??= new EventObserver(
            $ass_id,
            $user_id,
            $this,
            $this->dependencies->repositories()
        );
    }

    public function htmlProcessing(int $ass_id, int $user_id): HtmlService
    {
        return $this->instances[HtmlService::class][$ass_id][$user_id] ??= new HtmlService(
            $this->writingSettings($ass_id)->get(),
            $this->dependencies->taskApi($ass_id, $user_id)->correctionSettings()->get(),
            $this->dependencies->taskApi($ass_id, $user_id)->correctorComments(),
            $this->dependencies->systemApi()->htmlProcessing()
        );
    }

    public function imageProcessing(): ImageService
    {
        return $this->instances[ImageService::class] ??= new ImageService(
            $this->dependencies->systemApi()->imageSketch()
        );
    }

    public function import(int $ass_id, int $task_id, int $user_id): ImportService
    {
        return $this->instances[ImportService::class][$ass_id][$task_id][$user_id] ??= new ImportService(
            $task_id,
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->systemApi()->tempStorage(),
            $this->dependencies->systemApi()->session(__class__),
            $this->dependencies->systemApi()->config(),
            $this->dependencies->systemApi()->log(),
            $this->dependencies->systemApi()->user(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->essay($ass_id, $user_id, true),
            $this->language($user_id),
            [
                // add bavaria first for detection order
                ImportTypeBavaria::class => new ImportTypeBavaria(
                    $this->dependencies->systemApi()->spreadsheet(true),
                    $this->language($user_id)
                ),
                ImportTypeNrw::class => new ImportTypeNrw(
                    $this->language($user_id)
                )
            ]
        );
    }

    public function language(int $user_id): LanguageService
    {
        return $this->dependencies->systemApi()->language($user_id, __DIR__ . '/../Languages/');
    }

    public function manager(int $ass_id, int $task_id, int $user_id): ManagerService
    {
        return $this->instances[ManagerService::class][$ass_id][$task_id][$user_id] ??= new ManagerService(
            $ass_id,
            $task_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->taskApi($ass_id, $user_id)->tasks()
        );
    }

    public function writingPartProvider(int $ass_id, int $user_id): ?WritingProvider
    {
        return $this->instances[WritingProvider::class][$ass_id][$user_id] ?? new WritingProvider(
            $ass_id,
            $this->htmlProcessing($ass_id, $user_id),
            $this->dependencies->systemApi()->pdfProcessing(),
            $this->language($user_id),
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->properties(),
            $this->dependencies->taskApi($ass_id, $user_id)->tasks(),
            $this->dependencies->systemApi()->user()
        );
    }

    public function writingSettings(int $ass_id): WritingSettingsService
    {
        return $this->instances[WritingSettingsService::class][$ass_id] = new WritingSettingsService(
            $ass_id,
            $this->dependencies->repositories()
        );
    }

    public function writingSteps(int $ass_id, int $user_id): WritingStepsService
    {
        return $this->instances[WritingStepsService::class][$ass_id][$user_id] = new WritingStepsService(
            $this->essay($ass_id, $user_id),
            $this->dependencies->taskApi($ass_id, $user_id)->tasks(),
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->config(),
            $this->dependencies->systemApi()->tempStorage(),
            $this->dependencies->systemApi()->format($user_id),
        );
    }

    public function writerBridge(int $ass_id, int $user_id): WriterBridgeService
    {
        return $this->instances[WriterBridgeService::class][$ass_id][$user_id] ??= new WriterBridgeService(
            $ass_id,
            $user_id,
            $this->serviceVersion(),
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->entity(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->taskApi($ass_id, $user_id)->tasks(),
            $this->essay($ass_id, $user_id, false),
        );
    }

}
