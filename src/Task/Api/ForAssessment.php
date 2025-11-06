<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
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

    public function writerBridge(int $ass_id, int $user_id): ?AppBridge
    {
        return $this->internal->writerBridge($ass_id, $user_id);
    }
    public function correctorBridge(int $ass_id, int $user_id): ?AppBridge
    {
        return null;
    }

    public function writingPartProvider(int $ass_id, int $user_id): ?PdfPartProvider
    {
        // currently the task component provides no writing pdf parts
        return null;
    }

    public function correctionPartProvider(int $ass_id, int $user_id): ?PdfPartProvider
    {
        return $this->internal->correctionPartProvider($ass_id, $user_id);
    }
}
