<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

/**
 * Lifecycle manager for tasks
 */
interface TaskManager extends TaskReader
{
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
     * Delete the tasl-independent data of writers
     * @param int[] $writer_ids
     */
    public function deleteCommonWriterData(array $writer_ids): void;

    /**
     * Delete the task-independent data of correctors
     * @param int[] $corrector_ids
     */
    public function deleteCommonCorrectorData(array $corrector_ids): void;

    /**
     * Clone a task given by its id to a new assessment
     */
    public function clone(int $task_id, int $new_ass_id): void;
}
