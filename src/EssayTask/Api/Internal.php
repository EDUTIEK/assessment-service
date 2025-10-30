<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\AppBridges\WriterBridge as WriterBridgeService;
use Edutiek\AssessmentService\EssayTask\AssessmentStatus\Service as StatusService;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\GenerateEssayImages;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\Service as BackgroundTaskService;
use Edutiek\AssessmentService\EssayTask\Comments\Service as CommentsService;
use Edutiek\AssessmentService\EssayTask\ConstraintHandling\Provider as ConstraintProvider;
use Edutiek\AssessmentService\EssayTask\Essay\Service as EssayService;
use Edutiek\AssessmentService\EssayTask\EssayImage\FullService as EssayImageFullService;
use Edutiek\AssessmentService\EssayTask\EssayImage\Service as EssayImageService;
use Edutiek\AssessmentService\EssayTask\EssayImport\Bavaria;
use Edutiek\AssessmentService\EssayTask\EssayImport\Import;
use Edutiek\AssessmentService\EssayTask\EssayImport\Nrw;
use Edutiek\AssessmentService\EssayTask\EssayImport\Service as ImportService;
use Edutiek\AssessmentService\EssayTask\EventHandling\Observer as EventObserver;
use Edutiek\AssessmentService\EssayTask\HtmlProcessing\Service as HtmlService;
use Edutiek\AssessmentService\EssayTask\Manager\Service as ManagerService;
use Edutiek\AssessmentService\EssayTask\PdfOutput\Service as PdfOutput;
use Edutiek\AssessmentService\EssayTask\TaskSettings\Service as TaskSettingsService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\Service as WritingSettingsService;
use Edutiek\AssessmentService\System\BackgroundTask\Job;
use Edutiek\AssessmentService\System\Language\FullService as LanguageService;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

    public function assessmentStatus(int $ass_id): StatusService
    {
        return $this->instances[StatusService::class] = new StatusService(
            $ass_id,
            $this->dependencies->repositories()
        );
    }

    public function comments(): CommentsService
    {
        return $this->instances[CommentsService::class] ??= new CommentsService(
            $this->dependencies->systemApi()->imageSketch()
        );
    }

    public function backgroundTask(int $ass_id, string $class): Job
    {
        $jobs = [
            GenerateEssayImages::class => fn() => $this->instances[GenerateEssayImages::class] ??= new GenerateEssayImages(
                $this->dependencies->repositories()->essay(),
                $this->essayImage($ass_id),
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

    public function essay(int $ass_id, int $user_id, bool $as_admin = false): EssayService
    {
        return $this->instances[EssayService::class] = new EssayService(
            $as_admin,
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->taskApi($ass_id, $user_id)->tasks(),
            $this->backgroundTaskManager($ass_id, $user_id),
            $this->essayImage($ass_id),
            $this->language($user_id),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->eventDispatcher($ass_id, $user_id),
            $this->dependencies->constraintCollector($ass_id, $user_id),
        );
    }

    public function essayImage(int $ass_id): EssayImageService
    {
        return $this->instances[EssayImageFullService::class] = new EssayImageService(
            $this->dependencies->repositories()->essayImage(),
            $this->dependencies->repositories()->essay(),
            $this->writingSettings($ass_id)->get(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->systemApi()->pdfConverter(),
            $this->dependencies->systemApi()->pdfCreator(),
            $this->htmlProcessing(),
        );
    }

    public function eventObserver(int $ass_id, int $user_id): EventObserver
    {
        return $this->instances[EventObserver::class][$ass_id][$user_id] ??= new EventObserver(
            $ass_id,
            $user_id,
            $this
        );
    }

    public function htmlProcessing(): HtmlService
    {
        return $this->instances[HtmlService::class] ??= new HtmlService(
            $this->comments()
        );
    }

    public function import(int $ass_id, int $user_id, Import $import): ImportService
    {
        return $this->instances[ImportService::class] ??= new ImportService(
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->assessmentApi($ass_id, $user_id)->writer(),
            $this->dependencies->taskApi($ass_id, $user_id)->tasks(),
            $this->essay($ass_id, $user_id, true),
            $this->dependencies->systemApi()->user(),
            $this->language($user_id),
            $this->dependencies->systemApi()->config(),
            $user_id,
            $import,
            [
                'by' => fn($p) => new Bavaria($p, $import, $this->loadExcelDataFromFile(...)),
                'nrw' => fn($p) => new Nrw($p, $import),
            ]
        );

    }

    public function language(int $user_id): LanguageService
    {
        return $this->instances[LanguageService::class][$user_id] ??=
            $this->dependencies->systemApi()->loadLanguagFromFile($user_id, __DIR__ . '/../Languages/');
    }

    public function manager(int $ass_id, int $task_id, int $user_id): ManagerService
    {
        return $this->instances[ManagerService::class][$ass_id][$task_id][$user_id] ??= new ManagerService(
            $ass_id,
            $task_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
        );
    }

    private function loadExcelDataFromFile(string $filename): array
    {
        return IOFactory::load($filename)->getActiveSheet()->toArray();
    }

    public function pdfOutput(int $ass_id, int $user_id): PdfOutput
    {
        return $this->instances[PdfOutput::class] = new PdfOutput(
            $this->dependencies->assessmentApi($ass_id, $user_id)->pdfSettings(),
            $this->dependencies->repositories()->essayImage(),
            $this->dependencies->systemApi()->pdfCreator(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->htmlProcessing(),
            $this->writingSettings($ass_id),
            $this->dependencies->systemApi()->format($user_id),
            $this->essayImage($ass_id), // lazy
        );
    }

    public function writingSettings(int $ass_id): WritingSettingsService
    {
        return $this->instances[WritingSettingsService::class] = new WritingSettingsService(
            $ass_id,
            $this->dependencies->repositories()
        );
    }

    public function taskSettings(int $ass_id, int $task_id): TaskSettingsService
    {
        return $this->instances[TaskSettingsService::class][$ass_id][$task_id] ??= new TaskSettingsService(
            $ass_id,
            $task_id,
            $this->dependencies->repositories()
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
