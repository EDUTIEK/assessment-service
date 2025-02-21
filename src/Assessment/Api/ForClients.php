<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Permissions\ReadService as PermissionsReadService;
use Edutiek\AssessmentService\Assessment\Permissions\Service as PermissionsService;
use Edutiek\AssessmentService\Assessment\Supervision\FullService as SupervisionFullService;
use Edutiek\AssessmentService\Assessment\Supervision\Service as SupervisionService;
use Edutiek\AssessmentService\Assessment\WriterApp\OpenService as WriterAppOpenService;
use Edutiek\AssessmentService\Assessment\CorrectorApp\OpenService as CorrectorAppOpenService;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function writerApp($user_id): WriterAppOpenService
    {
        return $this->internal->writer($this->ass_id, $this->context_id, $user_id);
    }

    public function correctorApp($user_id): CorrectorAppOpenService
    {
        return $this->internal->corrector($this->ass_id, $this->context_id, $user_id);
    }

    public function supervision(): SupervisionFullService
    {
        return $this->instances[SupervisionService::class] ??= new SupervisionService(
            $this->ass_id,
            $this->context_id,
            $this->dependencies->repositories()
        );
    }

    public function permissions(int $user_id): PermissionsReadService
    {
        return $this->instances[PermissionsService::class][$user_id] ??= new PermissionsService(
            $this->ass_id,
            $this->context_id,
            $user_id,
            $this->dependencies->repositories()
        );
    }
}
