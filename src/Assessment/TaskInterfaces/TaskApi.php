<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

/**
 * CRUD manager for tasks of an assessment
 */
interface TaskApi
{
    public function taskManager(int $ass_id, int $user_id): TaskManager;
    public function writerBridge(int $ass_id, int $user_id): WriterBridge;
}
