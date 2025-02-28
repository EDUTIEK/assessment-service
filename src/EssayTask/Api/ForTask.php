<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\Task\TypeInterfaces\Api as TypeApi;
use Edutiek\AssessmentService\Task\TypeInterfaces\Manager as ManagerInterface;
use Edutiek\AssessmentService\EssayTask\Manager\Service as ManagerService;

class ForTask implements TypeApi
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function manager(int $task_id, int $user_id): ManagerInterface
    {
        return $this->instances[ManagerService::class] = new ManagerService(
            $task_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->internal->language($user_id),
        );
    }
}
