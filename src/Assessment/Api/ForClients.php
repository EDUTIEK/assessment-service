<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Alert\FullService as FullAlertService;
use Edutiek\AssessmentService\Assessment\AssessmentGrading\ReadService as AssessmentGradingReadService;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\FullService as CorrectionSettingsFullService;
use Edutiek\AssessmentService\Assessment\Corrector\FullService as CorrectorFullService;
use Edutiek\AssessmentService\Assessment\CorrectorApp\OpenService as CorrectorAppOpenService;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\DisabledGroup\FullService as DisabledGroupFullService;
use Edutiek\AssessmentService\Assessment\Format\FullService as FormatInterface;
use Edutiek\AssessmentService\Assessment\GradeLevel\FullService as gradeLevelFullService;
use Edutiek\AssessmentService\Assessment\Location\FullService as LocationFullService;
use Edutiek\AssessmentService\Assessment\LogEntry\FullService as FullLogEntryService;
use Edutiek\AssessmentService\Assessment\Manager\FullService as ManagerFullService;
use Edutiek\AssessmentService\Assessment\OrgaSettings\FullService as OrgaSettingsFullService;
use Edutiek\AssessmentService\Assessment\PdfSettings\FullService as PdfSettingsFullService;
use Edutiek\AssessmentService\Assessment\Permissions\ReadService as PermissionsReadService;
use Edutiek\AssessmentService\Assessment\Properties\FullService as PropertiesFullSrvice;
use Edutiek\AssessmentService\Assessment\WorkingTime\FullService as FullWorkingTime;
use Edutiek\AssessmentService\Assessment\WorkingTime\FullService as IndividualWorkingTime;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterFullService;
use Edutiek\AssessmentService\Assessment\WriterApp\OpenService as WriterAppOpenService;

readonly class ForClients
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Internal $internal
    ) {
    }

    public function correctionSettings(): CorrectionSettingsFullService
    {
        return $this->internal->correctionSettings($this->ass_id);
    }

    public function corrector(): CorrectorFullService
    {
        return $this->internal->corrector($this->ass_id);
    }

    public function correctorApp(int $context_id): CorrectorAppOpenService
    {
        return $this->internal->correctorApp($this->ass_id, $context_id, $this->user_id);
    }

    public function gradeLevel(): GradeLevelFullService
    {
        return $this->internal->gradeLevel($this->ass_id);
    }

    public function manager(): ManagerFullService
    {
        return $this->internal->manager($this->ass_id, $this->user_id);
    }

    public function location(): LocationFullService
    {
        return $this->internal->location($this->ass_id);
    }

    public function orgaSettings(): OrgaSettingsFullService
    {
        return $this->internal->orgaSettings($this->ass_id, $this->user_id);
    }

    public function pdfSettings(): PdfSettingsFullService
    {
        return $this->internal->pdfSettings($this->ass_id);
    }

    public function permissions(int $context_id): PermissionsReadService
    {
        return $this->internal->permissions($this->ass_id, $context_id, $this->user_id);
    }

    public function properties(): PropertiesFullSrvice
    {
        return $this->internal->properties($this->ass_id);
    }

    public function writer(): WriterFullService
    {
        return $this->internal->writer($this->ass_id, $this->user_id);
    }

    public function writerApp(int $context_id): WriterAppOpenService
    {
        return $this->internal->writerApp($this->ass_id, $context_id, $this->user_id);
    }

    public function format(OrgaSettings $orga): FormatInterface
    {
        return $this->internal->format($orga, $this->user_id);
    }

    public function workingTime(OrgaSettings $orga, Writer|IndividualWorkingTime|null $writer = null): FullWorkingTime
    {
        return $this->internal->workingTimeFactory($this->user_id)->workingTime($orga, $writer);
    }

    public function logEntry(): FullLogEntryService
    {
        return $this->internal->logEntry($this->ass_id);
    }

    public function alert(): FullAlertService
    {
        return $this->internal->alert($this->ass_id);
    }

    public function assessmentGrading(): AssessmentGradingReadService
    {
        return $this->internal->assessmentGrading($this->ass_id);
    }

    public function disabledGroup(): DisabledGroupFullService
    {
        return $this->internal->disabledGroup($this->ass_id);
    }
}
