<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\TypeInterfaces;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskType;

interface ApiFactory
{
    public function api(TaskType $type): Api;
}
