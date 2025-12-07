<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSummary;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Grading;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingProvider;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Checks\FullService as ChecksService;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;

readonly class Service implements FullService, GradingProvider
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
        return $this->repos->correctorSummary()->allByTaskIdAndWriterId($task_id, $writer_id);
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

    public function getForAssignment(CorrectorAssignment $assignment): CorrectorSummary
    {
        return  $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId()
        ) ?? $this->newForAssignment($assignment);
    }

    public function newForAssignment(CorrectorAssignment $assignment): CorrectorSummary
    {
        return $this->repos->correctorSummary()->new()
            ->setTaskId($assignment->getTaskId())
            ->setWriterId($assignment->getWriterId())
            ->setCorrectorId($assignment->getCorrectorId());
    }

    public function gradingsForTaskAndWriter(int $task_id, int $writer_id): array
    {
        $gradings = [
            GradingPosition::FIRST->value => null,
            GradingPosition::SECOND->value => null,
            GradingPosition::STITCH->value => null,
        ];

        $assignments = [];
        foreach ($this->repos->correctorAssignment()->allByTaskIdAndWriterId($task_id, $writer_id) as $assignment) {
            $assignments[$assignment->getCorrectorId()] = $assignment;
        }

        foreach ($this->repos->correctorSummary()->allByTaskIdAndWriterId($task_id, $writer_id) as $summary) {
            if (!empty($assignment = $assignments[$summary->getCorrectorId()] ?? null)) {
                $gradings[$assignment->getPosition()->value] = new Grading(
                    $summary->getWriterId(),
                    $summary->getTaskId(),
                    $summary->getCorrectorId(),
                    $assignment->getPosition(),
                    $summary->getGradingStatus(),
                    $summary->getEffectivePoints(),
                    $summary->getRequireOtherRevision()
                );
            }
        }

        return $gradings;
    }
}
