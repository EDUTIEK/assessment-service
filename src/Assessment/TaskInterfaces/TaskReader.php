<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

/**
 * Lifecycle manager for tasks
 */
interface TaskReader
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
     * Get the ids of all tasks in the assessment
     * @return int[]
     */
    public function allIds(): array;

    /**
     * Check if a task exists in the assessment
     */
    public function has(int $task_id): bool;

    /**
     * Get a task info by id
     */
    public function one(int $task_id): ?TaskInfo;

    /**
     * Get the first found task info
     */
    public function first(): ?TaskInfo;
}
