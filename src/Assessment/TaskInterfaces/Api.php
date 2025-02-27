<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

interface Api
{
    public function manager(int $ass_id): Manager;
}