<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

interface TypeApiFactory
{
    public function api(TaskType $type): TypeApi;
}
