<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\Assessment\Apps\WriterBridge as WriterBridgeInterface;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TypeApi;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TypeManager as ManagerInterface;
use Edutiek\AssessmentService\EssayTask\AppBridges\WriterBridge as WriterBridgeService;
use Edutiek\AssessmentService\EssayTask\Manager\Service as ManagerService;

readonly class ForServices implements TypeApi
{
    public function __construct(
        private Internal $internal
    ) {
    }

    public function manager(int $ass_id, int $task_id, int $user_id): ManagerInterface
    {
        return $this->internal->manager($ass_id, $task_id, $user_id);
    }

    public function writerBridge(int $ass_id, int $user_id): WriterBridgeInterface
    {
        return $this->internal->writerBridge($ass_id, $user_id);
    }
}
