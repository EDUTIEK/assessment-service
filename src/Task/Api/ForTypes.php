<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\EssayTask\TaskInterfaces\Api as TasksApi;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\Manager as ManagerInterface;
use Edutiek\AssessmentService\Task\Manager\Service as ManagerService;
use Edutiek\AssessmentService\EssayTask\TaskInterfaces\CorrectorAssignment as CorrectorAssignmentInterface;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ServiceForTypes as CorrectorAssignmentService;

class ForTypes implements TasksApi
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly Dependencies $dependencies
    ) {
    }

    public function correctorAssignments(): CorrectorAssignmentInterface
    {
        return $this->instances[CorrectorAssignmentService::class] ??= new CorrectorAssignmentService($this->ass_id, $this->dependencies->repositories());
    }
}
