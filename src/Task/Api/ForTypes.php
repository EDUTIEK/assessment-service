<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Manager as ManagerInterface;
use Edutiek\AssessmentService\Task\Manager\Service as ManagerService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\Service as CorrectorAssignmentService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as CorrectorAssignmentReadService;

class ForTypes
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly Dependencies $dependencies
    ) {
    }

    public function correctorAssignments(): CorrectorAssignmentReadService
    {
        return $this->instances[CorrectorAssignmentService::class] ??= new CorrectorAssignmentService($this->ass_id, $this->dependencies->repositories());
    }
}
