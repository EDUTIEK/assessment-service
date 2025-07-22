<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Corrector\FullService as CorrectorFullService;
use Edutiek\AssessmentService\Assessment\Corrector\Service as CorrectorService;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\FullService as CorrectionSettingsFullService;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\Service as CorrectionSettingsService;
use Edutiek\AssessmentService\Assessment\CorrectorApp\OpenService as CorrectorAppOpenService;
use Edutiek\AssessmentService\Assessment\GradeLevel\FullService as gradeLevelFullService;
use Edutiek\AssessmentService\Assessment\GradeLevel\Service as GradeLevelService;
use Edutiek\AssessmentService\Assessment\Manager\FullService as ManagerFullService;
use Edutiek\AssessmentService\Assessment\Manager\Service as ManagerService;
use Edutiek\AssessmentService\Assessment\Location\FullService as LocationFullService;
use Edutiek\AssessmentService\Assessment\Location\Service as LocationService;
use Edutiek\AssessmentService\Assessment\OrgaSettings\FullService as OrgaSettingsFullService;
use Edutiek\AssessmentService\Assessment\OrgaSettings\Service as OrgaSettingsService;
use Edutiek\AssessmentService\Assessment\PdfSettings\FullService as PdfSettingsFullService;
use Edutiek\AssessmentService\Assessment\PdfSettings\Service as PdfSettingsService;
use Edutiek\AssessmentService\Assessment\Permissions\ReadService as PermissionsReadService;
use Edutiek\AssessmentService\Assessment\Permissions\Service as PermissionsService;
use Edutiek\AssessmentService\Assessment\Properties\FullService as PropertiesFullSrvice;
use Edutiek\AssessmentService\Assessment\Properties\Service as PropertiesService;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterFullService;
use Edutiek\AssessmentService\Assessment\Writer\Service as WriterService;
use Edutiek\AssessmentService\Assessment\WriterApp\OpenService as WriterAppOpenService;
use Edutiek\AssessmentService\Assessment\Format\Service as Format;
use Edutiek\AssessmentService\Assessment\Format\FullService as FormatInterface;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\WorkingTime\FullService as FullWorkingTime;
use Edutiek\AssessmentService\Assessment\WorkingTime\Service as WorkingTime;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\Assessment\LogEntry\FullService as FullLogEntryService;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
use Edutiek\AssessmentService\Assessment\WorkingTime\IndividualWorkingTime;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function correctionSettings(): CorrectionSettingsFullService
    {
        return $this->instances[CorrectionSettingsService::class] = new CorrectionSettingsService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function corrector(): CorrectorFullService
    {
        return $this->instances[CorrectorFullService::class] ??= new CorrectorService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }


    public function correctorApp(int $context_id): CorrectorAppOpenService
    {
        return $this->internal->correctorApp($this->ass_id, $context_id, $this->user_id);
    }

    public function gradLevel(): GradeLevelFullService
    {
        return $this->instances[GradeLevelService::class] = new GradeLevelService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function manager(): ManagerFullService
    {
        return $this->instances[ManagerService::class] = new ManagerService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->internal->language($this->user_id),
            $this->dependencies->taskApi()->manager($this->ass_id, $this->user_id)
        );
    }

    public function location(): LocationFullService
    {
        return $this->instances[LocationService::class] = new LocationService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function orgaSettings(): OrgaSettingsFullService
    {
        return $this->instances[OrgaSettingsService::class] = new OrgaSettingsService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->internal->workingTimeFactory($this->user_id)
        );
    }

    public function pdfSettings(): PdfSettingsFullService
    {
        return $this->instances[PdfSettingsService::class] = new PdfSettingsService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function permissions(int $context_id): PermissionsReadService
    {
        return $this->instances[PermissionsService::class][$context_id] ??= new PermissionsService(
            $this->ass_id,
            $context_id,
            $this->user_id,
            $this->dependencies->repositories()
        );
    }

    public function properties(): PropertiesFullSrvice
    {
        return $this->instances[PropertiesService::class] ??= new PropertiesService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function writer(): WriterFullService
    {
        return $this->instances[WriterFullService::class] ??= new WriterService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->internal->workingTimeFactory($this->user_id),
            $this->logEntry()
        );
    }

    public function writerApp(int $context_id): WriterAppOpenService
    {
        return $this->internal->writerApp($this->ass_id, $context_id, $this->user_id);
    }

    public function format(OrgaSettings $orga): FormatInterface
    {
        return new Format(
            $this->internal->language($this->user_id),
            $this->dependencies->systemApi()->format($this->user_id),
            $orga
        );
    }

    public function workingTime(OrgaSettings $orga, Writer|IndividualWorkingTime|null $writer = null): FullWorkingTime
    {
        return $this->internal->workingTimeFactory($this->user_id)->workingTime(
            $orga,
            $writer
        );
    }

    public function logEntry(): FullLogEntryService
    {
        return $this->instances[LogEntryService::class] ??= new LogEntryService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->language(),
            $this->dependencies->systemApi()->user()
        );
    }
}
