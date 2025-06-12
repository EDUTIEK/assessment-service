<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\TaskInterfaces;

interface CorrectorAssignment
{
    /**
     * Get the corrector assignments for this task
     *
     * @return array<\Edutiek\AssessmentService\Task\Data\CorrectorAssignment>
     */
    public function all(): array;
}
