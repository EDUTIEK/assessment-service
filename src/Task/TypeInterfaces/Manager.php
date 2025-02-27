<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Task\TypeInterfaces;

interface Manager
{
    /**
     * Create the type specific entities for a new task
     */
    public function create(int $task_id): void;

    /**
     * Delete the type specific entities of  task
     */
    public function delete(): void;

    /**
     * Clone an assessment
     */
    public function clone(int $new_ass_id): void;
}