<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\CorrectorSummary;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Grading;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingPosition;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingProvider;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Checks\FullService as ChecksService;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;

readonly class Service implements ReadService, GradingProvider
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

    public function oneForAssignment(CorrectorAssignment $assignment): ?CorrectorSummary
    {
        return $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId()
        );
    }

    public function getForAssignment(CorrectorAssignment $assignment): CorrectorSummary
    {
        return $this->oneForAssignment($assignment)
            ?? $this->newForAssignment($assignment);
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

        $summaries = [];
        foreach ($this->repos->correctorSummary()->allByTaskIdAndWriterId($task_id, $writer_id) as $summary) {
            $summaries[$summary->getCorrectorId()] = $summary;
        }

        foreach ($this->repos->correctorAssignment()->allByTaskIdAndWriterId($task_id, $writer_id) as $assignment) {
            $summary = $summaries[$assignment->getCorrectorId()] ?? null;

            $gradings[$assignment->getPosition()->value] = new Grading(
                $assignment->getWriterId(),
                $assignment?->getTaskId(),
                $assignment->getCorrectorId(),
                $assignment->getPosition(),
                $summary?->getGradingStatus() ?? GradingStatus::OPEN,
                $summary?->getEffectivePoints(),
                $summary?->getRequireOtherRevision() ?? false
            );
        }

        return $gradings;
    }
}
