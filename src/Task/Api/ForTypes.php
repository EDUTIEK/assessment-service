<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Manager as ManagerInterface;
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

    public function correctorAssignments(): CorrectorAssignmentReadService
    {
        return $this->internal->correctorAssignments($this->ass_id, $this->user_id);
    }

    public function correctorComment(int $task_id, int $writer_id): CorrectorCommentFullService
    {
        return $this->instances[CorrectorCommentService::class][$task_id][$writer_id] = new CorrectorCommentService($task_id, $writer_id, $this->dependencies->repositories());
    }

    public function correctionSettings(): CorrectionSettingsReadService
    {
        return $this->internal->correctionSettings($this->ass_id, $this->user_id);
    }
}
