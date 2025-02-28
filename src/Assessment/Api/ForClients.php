<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\CorrectorApp\OpenService as CorrectorAppOpenService;
use Edutiek\AssessmentService\Assessment\Manager\FullService as ManagerFullService;
use Edutiek\AssessmentService\Assessment\Manager\Service as ManagerService;
use Edutiek\AssessmentService\Assessment\Permissions\ReadService as PermissionsReadService;
use Edutiek\AssessmentService\Assessment\Permissions\Service as PermissionsService;
use Edutiek\AssessmentService\Assessment\Supervision\FullService as SupervisionFullService;
use Edutiek\AssessmentService\Assessment\Supervision\Service as SupervisionService;
use Edutiek\AssessmentService\Assessment\WriterApp\OpenService as WriterAppOpenService;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly int $user_id,
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function correctorApp(): CorrectorAppOpenService
    {
        return $this->internal->corrector($this->ass_id, $this->context_id, $this->user_id);
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

    public function permissions(): PermissionsReadService
    {
        return $this->instances[PermissionsService::class] ??= new PermissionsService(
            $this->ass_id,
            $this->context_id,
            $this->user_id,
            $this->dependencies->repositories()
        );
    }

    public function supervision(): SupervisionFullService
    {
        return $this->instances[SupervisionService::class] ??= new SupervisionService(
            $this->ass_id,
            $this->context_id,
            $this->user_id,
            $this->dependencies->repositories(),
            $this->internal->language($this->user_id),
        );
    }

    public function writerApp(): WriterAppOpenService
    {
        return $this->internal->writer($this->ass_id, $this->context_id, $this->user_id);
    }
}
