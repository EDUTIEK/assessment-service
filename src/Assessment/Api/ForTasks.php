<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Authentication\FullService as AuthenticationFullService;
use Edutiek\AssessmentService\Assessment\Authentication\Service as AuthenticationService;
use Edutiek\AssessmentService\Assessment\Apps\RestService as RestService;
use Edutiek\AssessmentService\Assessment\Apps\Service as AppService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Assessment\Writer\Service as WriterService;
use Edutiek\AssessmentService\Assessment\LogEntry\TasksService as LogEntryTasksService;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\Service as CorrectionSettingsService;
use Edutiek\AssessmentService\Assessment\PdfSettings\FullService as PdfSettingsFullService;
use Edutiek\AssessmentService\Assessment\PdfSettings\Service as PdfSettingsService;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as AssessmentGradingService;
use Edutiek\AssessmentService\Assessment\CorrectionProcess\FullService as CorrectionProcessService;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorReadService;
use Edutiek\AssessmentService\Assessment\Corrector\Service as CorrectorService;

class ForTasks
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function writer(): WriterReadService
    {
        return $this->internal->writer($this->ass_id, $this->user_id);
    }

    public function corrector(): CorrectorReadService
    {
        return $this->instances[CorrectorService::class] ??= new CorrectorService(
            $this->ass_id,
            $this->dependencies->repositories(),
        );
    }

    public function logEntry(): LogEntryTasksService
    {
        return $this->internal->logEntry($this->ass_id);
    }

    public function correctionSettings(): CorrectionSettingsReadService
    {
        return $this->instances[CorrectionSettingsService::class] ??= new CorrectionSettingsService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function pdfSettings(): PdfSettingsFullService
    {
        return $this->instances[PdfSettingsService::class] = new PdfSettingsService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function assessment_grading(): AssessmentGradingService
    {
        return $this->internal->assessment_grading($this->ass_id);
    }

    public function correction_process(): CorrectionProcessService
    {
        return $this->internal->correction_process($this->ass_id);
    }
}
