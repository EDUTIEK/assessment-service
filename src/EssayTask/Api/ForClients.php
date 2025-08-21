<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\Essay\FullService as EssayFullService;
use Edutiek\AssessmentService\EssayTask\Essay\Service as EssayService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\Service as WritingSettingsService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\FullService as WritingSettingsFullService;
use Edutiek\AssessmentService\EssayTask\AssessmentStatus\Service as StatusService;
use Edutiek\AssessmentService\EssayTask\AssessmentStatus\FullService as StatusFullService;
use Edutiek\AssessmentService\EssayTask\TaskSettings\Service as TaskSettingsService;
use Edutiek\AssessmentService\EssayTask\TaskSettings\FullService as TaskSettingsFullService;
use Edutiek\AssessmentService\EssayTask\PdfInput\FullService as FullPdfInput;
use Edutiek\AssessmentService\EssayTask\PdfInput\Service as PdfInput;
use Edutiek\AssessmentService\EssayTask\EssayImage\FullService as EssayImageFullService;
use Edutiek\AssessmentService\EssayTask\EssayImage\Service as EssayImageService;
use Edutiek\AssessmentService\System\BackgroundTask\Job;
use Edutiek\AssessmentService\System\BackgroundTask\Manager as BackgroundTaskManager;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\Service as BackgroundTaskService;
use Edutiek\AssessmentService\EssayTask\PdfOutput\FullService as FullPdfOutput;
use Edutiek\AssessmentService\EssayTask\PdfOutput\Service as PdfOutput;
use Edutiek\AssessmentService\EssayTask\PdfOutput\LazyService as LazyPdfOutput;
use LongEssayPDFConverter\ImageMagick\PDFImage;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\GenerateEssayImages;

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

    public function essay(): EssayFullService
    {
        return $this->instances[EssayFullService::class] = new EssayService(
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($this->ass_id, $this->user_id)->writer()
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

    public function pdfInput(): FullPdfInput
    {
        return $this->instances[PdfInput::class] ??= new PdfInput(
            $this->essay(),
            $this->backgroundTaskManager(),
            $this->essayImage(),
            fn(int $task_id, int $writer_id) => $this->dependencies->taskApi($this->ass_id, $this->user_id)->correctorComment($task_id, $writer_id),
            $this->internal->language($this->user_id),
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
            new PDFImage(),
            $this->dependencies->repositories()->essayImage(),
            new LazyPdfOutput($this->pdfOutput(...)),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->repositories()->essay(),
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
