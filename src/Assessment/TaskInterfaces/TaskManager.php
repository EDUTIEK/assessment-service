<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

/**
 * Lifecycle manager for tasks
 */
interface TaskManager
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

    /**
     * Create a new task for the assessment and return its id
     * The id and position of the input information should be null and is ignored
     */
    public function create(TaskInfo $info): int;

    /**
     * Delete a task of the assessment given by its id
     */
    public function delete(int $task_id): void;

    /**
     * Clone a task given by its id to a new assessment
     */
    public function clone(int $task_id, int $new_ass_id): void;
}
