<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\AssessmentStatus\FullService as StatusFullService;
use Edutiek\AssessmentService\EssayTask\AssessmentStatus\Service as StatusService;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\GenerateEssayImages;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\Service as BackgroundTaskService;
use Edutiek\AssessmentService\EssayTask\Essay\ClientService as EssayClientService;
use Edutiek\AssessmentService\EssayTask\Essay\Service as EssayService;
use Edutiek\AssessmentService\EssayTask\EssayImage\FullService as EssayImageFullService;
use Edutiek\AssessmentService\EssayTask\EssayImage\Service as EssayImageService;
use Edutiek\AssessmentService\EssayTask\PdfOutput\FullService as FullPdfOutput;
use Edutiek\AssessmentService\EssayTask\PdfOutput\Service as PdfOutput;
use Edutiek\AssessmentService\EssayTask\TaskSettings\FullService as TaskSettingsFullService;
use Edutiek\AssessmentService\EssayTask\TaskSettings\Service as TaskSettingsService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\FullService as WritingSettingsFullService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\Service as WritingSettingsService;
use Edutiek\AssessmentService\System\BackgroundTask\Job;
use Edutiek\AssessmentService\System\BackgroundTask\Manager as BackgroundTaskManager;
use Edutiek\AssessmentService\EssayTask\EssayImport\Service as ImportService;
use Edutiek\AssessmentService\EssayTask\EssayImport\FullService as FullImportService;
use Edutiek\AssessmentService\EssayTask\EssayImport\Import;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Edutiek\AssessmentService\EssayTask\EssayImport\Nrw;
use Edutiek\AssessmentService\EssayTask\EssayImport\Bavaria;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly Dependencies $dependencies,
        private readonly Internal $internal,
    ) {
    }

    public function essay(bool $as_admin = false): EssayClientService
    {
        return $this->instances[EssayService::class] = new EssayService(
            $as_admin,
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($this->ass_id, $this->user_id)->writer(),
            $this->dependencies->taskApi($this->ass_id, $this->user_id)->tasks(),
            $this->backgroundTaskManager(),
            $this->essayImage(),
            $this->internal->language($this->user_id),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->eventDispatcher($this->ass_id, $this->user_id),
            $this->dependencies->constraintCollector($this->ass_id, $this->user_id),
        );
    }

    public function writingSettings(): WritingSettingsFullService
    {
        return $this->instances[WritingSettingsService::class] = new WritingSettingsService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function assessmentStatus(): StatusFullService
    {
        return $this->instances[StatusService::class] = new StatusService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function taskSettings(int $task_id): TaskSettingsFullService
    {
        return $this->instances[TaskSettingsService::class][$task_id] ??= new TaskSettingsService(
            $this->ass_id,
            $task_id,
            $this->dependencies->repositories()
        );
    }

    public function backgroundTask(string $class): Job
    {
        $jobs = [
            GenerateEssayImages::class => fn() => $this->instances[GenerateEssayImages::class] ??= new GenerateEssayImages(
                $this->dependencies->repositories()->essay(),
                $this->essayImage(),
            ),
        ];

        return $jobs[$class]();
    }

    public function import(Import $import): FullImportService
    {
        return $this->instances[ImportService::class] ??= new ImportService(
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->assessmentApi($this->ass_id, $this->user_id)->writer(),
            $this->dependencies->taskApi($this->ass_id, $this->user_id)->tasks(),
            $this->essay(true),
            $this->dependencies->systemApi()->user(),
            $this->user_id,
            $import,
            [
                'by' => fn($p) => new Bavaria($p, $import, $this->loadExcelDataFromFile(...)),
                'nrw' => fn($p) => new Nrw($p, $import),
            ]
        );
    }

    private function loadExcelDataFromFile(string $filename): array
    {
        return IOFactory::load($filename)->getActiveSheet()->toArray();
    }

    private function backgroundTaskManager(): BackgroundTaskManager
    {
        return $this->instances[BackgroundTaskService::class] ??= new BackgroundTaskService(
            $this->ass_id,
            $this->user_id,
            $this->dependencies->systemApi()->backgroundTask(),
        );
    }

    private function essayImage(): EssayImageFullService
    {
        return $this->instances[EssayImageFullService::class] = new EssayImageService(
            $this->dependencies->repositories()->essayImage(),
            $this->dependencies->repositories()->essay(),
            $this->writingSettings()->get(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->systemApi()->pdfConverter(),
            $this->dependencies->systemApi()->pdfCreator(),
            $this->internal->htmlProcessing(),
        );
    }

    private function pdfOutput(): FullPdfOutput
    {
        return $this->instances[PdfOutput::class] = new PdfOutput(
            $this->dependencies->assessmentApi($this->ass_id, $this->user_id)->pdfSettings(),
            $this->dependencies->repositories()->essayImage(),
            $this->dependencies->systemApi()->pdfCreator(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->internal->htmlProcessing(),
            $this->writingSettings(),
            $this->dependencies->systemApi()->format($this->user_id),
            $this->essayImage(), // lazy
        );
    }
}
