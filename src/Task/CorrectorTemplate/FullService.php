<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorTemplate;

use Edutiek\AssessmentService\Task\Data\CorrectorTemplate;

interface FullService
{
    public function getByTaskIdAndCorrectorId(int $task_id, int $corrector_id): CorrectorTemplate;
    public function save(CorrectorTemplate $template);
}
