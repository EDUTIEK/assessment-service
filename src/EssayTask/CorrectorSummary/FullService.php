<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\CorrectorSummary;

use Edutiek\AssessmentService\EssayTask\Data\CorrectorSummary;

interface FullService
{
    /** @return CorrectorSummary[] */
    public function all(): array;
    /** @return CorrectorSummary[] */
    public function allByWriterIdAndTaskId(int $writer_id, int $task_id): array;
}