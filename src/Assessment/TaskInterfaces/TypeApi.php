<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

use Edutiek\AssessmentService\Assessment\Apps\WriterBridge;

/**
 * API of a Task Typ to be uses by Assessments
 */
interface TypeApi
{
    public function writerBridge(int $ass_id, int $user_id): WriterBridge;
    public function manager(int $ass_id, int $task_id, int $user_id): TypeManager;
}
