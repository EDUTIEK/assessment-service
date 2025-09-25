<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Task\Manager\ReadService as ManagerReadService;
use Edutiek\AssessmentService\Task\Manager\Service as ManagerService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\Service as CorrectorAssignmentService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as CorrectorAssignmentReadService;
use Edutiek\AssessmentService\Task\CorrectorComment\FullService as CorrectorCommentFullService;
use Edutiek\AssessmentService\Task\CorrectorComment\Service as CorrectorCommentService;
use Edutiek\AssessmentService\Task\CorrectionSettings\ReadService as CorrectionSettingsReadService;

class ForTypes
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly Internal $internal,
        private readonly Dependencies $dependencies
    ) {
    }

    public function tasks(): ManagerReadService
    {
        return $this->instances[ManagerService::class] ??= new ManagerService(
            $this->ass_id,
            $this->user_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->typeApis(),
            $this->internal->language("de")
        );
    }

    public function correctorAssignments(): CorrectorAssignmentReadService
    {
        return $this->internal->correctorAssignments($this->ass_id, $this->user_id);
    }

    public function correctionSettings(): CorrectionSettingsReadService
    {
        return $this->internal->correctionSettings($this->ass_id, $this->user_id);
    }
}
