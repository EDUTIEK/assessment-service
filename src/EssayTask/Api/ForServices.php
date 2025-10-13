<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\TypeApi;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TypeManager as ManagerInterface;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\WriterBridge as WriterBridgeInterface;
use Edutiek\AssessmentService\EssayTask\Manager\Service as ManagerService;
use Edutiek\AssessmentService\EssayTask\WriterBridge\Service as WriterBridgeService;

class ForServices implements TypeApi
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function manager(int $ass_id, int $task_id, int $user_id): ManagerInterface
    {
        return $this->instances[ManagerService::class][$ass_id][$task_id][$user_id] ??= new ManagerService(
            $ass_id,
            $task_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
        );
    }

    public function writerBridge(int $ass_id, int $user_id): WriterBridgeInterface
    {
        return $this->instances[WriterBridgeService::class][$ass_id][$user_id] ??= new WriterBridgeService(
            $ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
        );
    }
}
