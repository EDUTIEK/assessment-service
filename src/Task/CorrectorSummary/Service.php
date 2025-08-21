<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSummary;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;

readonly class Service implements FullService
{
    public function __construct(
        private int $task_id,
        private Repositories $repos
    ) {
    }

    public function all(): array
    {
        return $this->repos->correctorSummary()->allByTaskId($this->task_id);
    }

    public function allByWriterId(int $writer_id): array
    {
        return $this->repos->correctorSummary()->allByTaskIdAndWriterIds($this->task_id, [$writer_id]);
    }

    public function allByCorrectorId(int $corrector_id): array
    {
        return $this->repos->correctorSummary()->allByTaskIdAndCorrectorId($this->task_id, $corrector_id);
    }

}
