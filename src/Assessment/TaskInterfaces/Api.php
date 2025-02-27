<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

interface Api
{
    public function tasks(int $ass_id): Tasks;
}