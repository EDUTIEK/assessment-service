<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Api as TasksApi;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\Manager as ManagerInterface;
use Edutiek\AssessmentService\Task\Manager\Service as ManagerService;

class ForAssessment implements TasksApi
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    public function manager(int $ass_id, int $user_id): ManagerInterface
    {
        return $this->instances[ManagerService::class] = new ManagerService(
            $ass_id,
            $user_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->typeApis()
        );
    }
}
