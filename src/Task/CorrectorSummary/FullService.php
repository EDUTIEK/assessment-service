<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSummary;

use Edutiek\AssessmentService\Task\Data\CorrectorSummary;

interface FullService
{
    /** @return CorrectorSummary[] */
    public function all(): array;
    /** @return CorrectorSummary[] */
    public function allByWriterId(int $writer_id): array;
    /** @return CorrectorSummary[] */
    public function allByCorrectorId(int $corrector_id): array;
}