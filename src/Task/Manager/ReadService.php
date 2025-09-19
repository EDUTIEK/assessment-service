<?php

namespace Edutiek\AssessmentService\Task\Manager;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskInfo;

interface ReadService
{
    /**
     * Get the number of tasks in this assessment
     */
    public function count(): int;

    /**
     * Get the basic info of all tasks of the assessment
     * The array is ordered by the tasks positions
     *
     * @return TaskInfo[]
     */
    public function all(): array;

    /**
     * Check if a task exists in the assessment
     */
    public function has($task_id): bool;

    /**
     * Get a task info by id
     */
    public function one(int $task_id): ?TaskInfo;

    /**
     * Get the first found task info
     */
    public function first(): ?TaskInfo;
}