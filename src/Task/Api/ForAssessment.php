<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Tasks as TasksInterface;
use Edutiek\AssessmentService\Task\Tasks\Service as TasksService;

class ForAssessment
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly Dependencies $dependencies
    ) {
    }

    public function tasks(): TasksInterface
    {
        return $this->instances[TasksService::class] = new TasksService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage()
        );
    }
}