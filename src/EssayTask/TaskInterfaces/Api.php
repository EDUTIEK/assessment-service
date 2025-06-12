<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\TaskInterfaces;

interface Api
{
    public function correctorAssignments(): CorrectorAssignment;
}
