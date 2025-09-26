<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSummary;

use Edutiek\AssessmentService\Task\Data\CorrectorSummary;

interface FullService
{
    /** @return CorrectorSummary[] */
    public function allByTaskId(int $task_id): array;
    /** @return CorrectorSummary[] */
    public function allByTaskIdAndWriterId(int $task_id, int $writer_id): array;
    /** @return CorrectorSummary[] */
    public function allByTaskIdAndCorrectorId(int $task_id, int $corrector_id): array;
}
