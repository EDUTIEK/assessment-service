<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\Apps\WriterBridge as WriterBridgeInterface;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskApi as TasksApi;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager as ManagerInterface;
use Edutiek\AssessmentService\Task\AppBridges\Writer as WriterBridgeService;
use Edutiek\AssessmentService\Task\Manager\Service as ManagerService;

class ForAssessment implements TasksApi
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function taskManager(int $ass_id, int $user_id): ManagerInterface
    {
        return $this->instances[ManagerService::class][$ass_id][$user_id] ??= new ManagerService(
            $ass_id,
            $user_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->typeApis(),
            $this->internal->language("de"),
        );
    }

    public function writerBridge(int $ass_id, int $user_id): WriterBridgeInterface
    {
        return $this->instances[WriterBridgeService::class][$ass_id][$user_id] ??= new WriterBridgeService(
            $ass_id,
            $user_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->internal->language("de"),
        );
    }
}
