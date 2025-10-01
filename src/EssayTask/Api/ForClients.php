<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\AssessmentStatus\FullService as StatusFullService;
use Edutiek\AssessmentService\EssayTask\AssessmentStatus\Service as StatusService;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\GenerateEssayImages;
use Edutiek\AssessmentService\EssayTask\BackgroundTask\Service as BackgroundTaskService;
use Edutiek\AssessmentService\EssayTask\Essay\FullService as EssayFullService;
use Edutiek\AssessmentService\EssayTask\Essay\Service as EssayService;
use Edutiek\AssessmentService\EssayTask\EssayImage\FullService as EssayImageFullService;
use Edutiek\AssessmentService\EssayTask\EssayImage\Service as EssayImageService;
use Edutiek\AssessmentService\EssayTask\PdfInput\FullService as FullPdfInput;
use Edutiek\AssessmentService\EssayTask\PdfInput\Service as PdfInput;
use Edutiek\AssessmentService\EssayTask\PdfOutput\FullService as FullPdfOutput;
use Edutiek\AssessmentService\EssayTask\PdfOutput\Service as PdfOutput;
use Edutiek\AssessmentService\EssayTask\TaskSettings\FullService as TaskSettingsFullService;
use Edutiek\AssessmentService\EssayTask\TaskSettings\Service as TaskSettingsService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\FullService as WritingSettingsFullService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\Service as WritingSettingsService;
use Edutiek\AssessmentService\System\BackgroundTask\Job;
use Edutiek\AssessmentService\System\BackgroundTask\Manager as BackgroundTaskManager;

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
            $this->dependencies->assessmentApi($this->ass_id, $this->user_id)->writer(),
            $this->dependencies->taskApi($this->ass_id, $this->user_id)->tasks(),
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

    public function pdfInput(bool $as_admin): FullPdfInput
    {
        return $this->instances[PdfInput::class][(string) $as_admin] ??= new PdfInput(
            $as_admin,
            $this->essay(),
            $this->backgroundTaskManager(),
            $this->essayImage(),
            $this->internal->language($this->user_id),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->eventDispatcher($this->ass_id, $this->user_id),
            $this->dependencies->constraintCollector($this->ass_id, $this->user_id),
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
