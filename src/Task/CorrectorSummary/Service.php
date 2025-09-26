<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSummary;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Checks\FullService as ChecksService;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;

readonly class Service implements FullService
{
    public function __construct(
        private ChecksService $checks,
        private Repositories $repos
    ) {
    }

    public function allByTaskId($task_id): array
    {
        $this->checkTaskScope($task_id);
        return $this->repos->correctorSummary()->allByTaskId($task_id);
    }

    public function allByTaskIdAndWriterId(int $task_id, int $writer_id): array
    {
        $this->checkTaskScope($task_id);
        return $this->repos->correctorSummary()->allByTaskIdAndWriterIds($task_id, [$writer_id]);
    }

    public function allByTaskIdAndCorrectorId(int $task_id, int $corrector_id): array
    {
        $this->checkTaskScope($task_id);
        return $this->repos->correctorSummary()->allByTaskIdAndCorrectorId($task_id, $corrector_id);
    }

    public function checkTaskScope(int $task_id): void
    {
        if (!$this->checks->hasTask($task_id)) {
            throw new ApiException('wrong task_id', ApiException::ID_SCOPE);
        }
    }
}
