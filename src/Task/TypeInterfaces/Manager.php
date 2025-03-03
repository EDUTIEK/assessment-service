<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\TypeInterfaces;

interface Manager
{
    /**
     * Create the type specific entities for a new task
     */
    public function create(): void;

    /**
     * Delete the type specific entities of task
     */
    public function delete(): void;

    /**
     * Clone the type specific entities to a new task
     */
    public function clone(int $new_ass_id, int $new_task_id): void;
}
