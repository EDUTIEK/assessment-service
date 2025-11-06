<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\Api\ComponentApi;

/**
 * API of a Task Typ to be uses by Assessments
 */
interface TypeApi extends ComponentApi
{
    public function manager(int $ass_id, int $task_id, int $user_id): TypeManager;
}
