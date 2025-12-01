<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\Api\ComponentApi;

/**
 * CRUD manager for tasks of an assessment
 */
interface TaskApi extends ComponentApi
{
    public function taskManager(int $ass_id, int $user_id): TaskManager;
    public function gradingProvider(int $ass_id, int $user_id): GradingProvider;
}
