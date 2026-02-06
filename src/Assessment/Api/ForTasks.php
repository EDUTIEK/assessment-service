<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as AssessmentGradingService;
use Edutiek\AssessmentService\Assessment\CorrectionProcess\FullService as CorrectionProcessService;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as CorrectionSettingsReadService;
use Edutiek\AssessmentService\Assessment\Corrector\FullService as CorrectorService;
use Edutiek\AssessmentService\Assessment\LogEntry\TasksService as LogEntryTasksService;
use Edutiek\AssessmentService\Assessment\PdfSettings\FullService as PdfSettingsFullService;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\Assessment\Permissions\ReadService as PermissionsReadService;
use Edutiek\AssessmentService\Assessment\Location\ReadService as LocationService;
use Edutiek\AssessmentService\Assessment\OrgaSettings\ReadService as OrgaSettingsService;
use Edutiek\AssessmentService\Assessment\Properties\ReadService as PropertiesReadService;

readonly class ForTasks
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Internal $internal
    ) {
    }

    public function writer(): WriterService
    {
        return $this->internal->writer($this->ass_id, $this->user_id);
    }

    public function corrector(): CorrectorService
    {
        return $this->internal->corrector($this->ass_id, $this->user_id);
    }

    public function logEntry(): LogEntryTasksService
    {
        return $this->internal->logEntry($this->ass_id);
    }

    public function correctionSettings(): CorrectionSettingsReadService
    {
        return $this->internal->correctionSettings($this->ass_id, $this->user_id);
    }

    public function pdfSettings(): PdfSettingsFullService
    {
        return $this->internal->pdfSettings($this->ass_id);
    }

    public function properties(): PropertiesReadService
    {
        return $this->internal->properties($this->ass_id);
    }

    public function assessmentGrading(): AssessmentGradingService
    {
        return $this->internal->assessmentGrading($this->ass_id);
    }

    public function correctionProcess(): CorrectionProcessService
    {
        return $this->internal->correctionProcess($this->ass_id, $this->user_id);
    }

    public function location(): LocationService
    {
        return $this->internal->location($this->ass_id);
    }

    public function orgaSettings(): OrgaSettingsService
    {
        return $this->internal->orgaSettings($this->ass_id, $this->user_id);
    }
}
