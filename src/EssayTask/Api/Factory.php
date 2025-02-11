<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

class Factory
{
    private array $instances = [];

    public function __construct(private readonly Dependencies $dependencies)
    {
    }

    /**
     * Get the API for client systems
     * @param int $task_id  id of the task
     */
    public function forClients(int $task_id): ForClients
    {
        return $this->instances[ForClients::class][$task_id] ??= new ForClients(
            $task_id,
            $this->dependencies
        );
    }

    /**
     * Get the API for peer services
     * @param int $task_id  id of the task
     */
    public function forService(int $task_id): ForServices
    {
        return $this->instances[ForServices::class][$task_id] ??= new ForServices(
            $task_id,
            $this->dependencies
        );
    }
}
