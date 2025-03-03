<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\TypeInterfaces;

interface Api
{
    public function manager(int $ass_id, int $task_id, int $user_id): Manager;
}
