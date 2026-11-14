<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

interface TaskChecks
{
    /**
     * Check if assessment has the task
     */
    public function hasTask(int $task_id): bool;

    /**
     * Check if assessment has the writer
     */
    public function hasWriter(int $writer_id): bool;

    /**
     * Check if assessment has the corrector
     */
    public function hasCorrector(int $corrector_id): bool;

    /**
     * Check if corrector is assigned to the writer for the task
     */
    public function isAssigned(int $writer_id, int $corrector_id, int $task_id): bool;
}
