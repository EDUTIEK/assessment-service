<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as AssessmentGradingService;
use Edutiek\AssessmentService\Assessment\CorrectionProcess\FullService as CorrectionProcessService;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorReadService;
use Edutiek\AssessmentService\Assessment\LogEntry\TasksService as LogEntryTasksService;
use Edutiek\AssessmentService\Assessment\PdfSettings\FullService as PdfSettingsFullService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;

readonly class ForTasks
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Internal $internal
    ) {
    }

    public function writer(): WriterReadService
    {
        return $this->internal->writer($this->ass_id, $this->user_id);
    }

    public function corrector(): CorrectorReadService
    {
        return $this->internal->corrector($this->ass_id);
    }

    public function logEntry(): LogEntryTasksService
    {
        return $this->internal->logEntry($this->ass_id);
    }

    public function correctionSettings(): CorrectionSettingsReadService
    {
        return $this->internal->correctionSettings($this->ass_id);
    }

    public function pdfSettings(): PdfSettingsFullService
    {
        return $this->internal->pdfSettings($this->ass_id);
    }

    public function assessmentGrading(): AssessmentGradingService
    {
        return $this->internal->assessmentGrading($this->ass_id);
    }

    public function correctionProcess(): CorrectionProcessService
    {
        return $this->internal->correctionProcess($this->ass_id, $this->user_id);
    }
}
