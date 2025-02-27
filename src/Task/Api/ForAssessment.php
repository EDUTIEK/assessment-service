<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Api as TasksApi;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\Manager as TasksInterface;
use Edutiek\AssessmentService\Task\Manager\Service as TasksService;

class ForAssessment implements TasksApi
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    ) {
    }

    public function manager($ass_id): TasksInterface
    {
        return $this->instances[TasksService::class] = new TasksService(
            $ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage()
        );
    }
}