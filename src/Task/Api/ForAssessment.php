<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\Apps\WriterBridge as WriterBridgeInterface;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskApi as TasksApi;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager as ManagerInterface;

readonly class ForAssessment implements TasksApi
{
    public function __construct(
        private Internal $internal
    ) {
    }

    public function taskManager(int $ass_id, int $user_id): ManagerInterface
    {
        return $this->internal->taskManager($ass_id, $user_id);
    }

    public function writerBridge(int $ass_id, int $user_id): WriterBridgeInterface
    {
        return $this->internal->writerBridge($ass_id, $user_id);
    }
}
