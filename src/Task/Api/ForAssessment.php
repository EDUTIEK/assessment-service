<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\PdfCreation\PdfPartProvider;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingProvider;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskApi as TaskApi;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager as ManagerInterface;

readonly class ForAssessment implements TaskApi
{
    public function __construct(
        private Internal $internal
    ) {
    }

    public function taskManager(int $ass_id, int $user_id): ManagerInterface
    {
        return $this->internal->taskManager($ass_id, $user_id);
    }

    public function gradingProvider(int $ass_id, int $user_id): GradingProvider
    {
        return $this->internal->correctorSummary($ass_id, $user_id);
    }

    public function writerBridge(int $ass_id, int $user_id): ?AppBridge
    {
        return $this->internal->writerBridge($ass_id, $user_id);
    }

    public function correctorBridge(int $ass_id, int $user_id): ?AppBridge
    {
        return $this->internal->correctorBridge($ass_id, $user_id);
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
