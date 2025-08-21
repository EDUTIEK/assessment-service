<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Task\RatingCriterion;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\RatingCriterion\FullService;
use Edutiek\AssessmentService\Task\Data\RatingCriterion;
use Edutiek\AssessmentService\Task\Api\ApiException;

readonly class Service implements FullService
{
    public function __construct(
        private int $task_id,
        private Repositories $repos
    ) {
    }

    public function allByCorrectorId(?int $corrector_id): array
    {
        return $this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($this->task_id, $corrector_id);
    }

    public function new(): RatingCriterion
    {
        return $this->repos->ratingCriterion()->new()->setTaskId($this->task_id);
    }

    public function save(RatingCriterion $criterion) : void
    {
        $this->checkScope($criterion);
        $this->repos->ratingCriterion()->save($criterion);
    }

    private function checkScope(RatingCriterion $criterion)
    {
        if ($criterion->getTaskId() !== $this->task_id) {
            throw new ApiException("wrong task_id", ApiException::ID_SCOPE);
        }
    }

    public function one(int $criterion_id): ?RatingCriterion
    {
        return $this->repos->ratingCriterion()->one($criterion_id);
    }

    public function delete(RatingCriterion $criterion)
    {
        $this->checkScope($criterion);
        $this->repos->ratingCriterion()->delete($criterion->getId());
    }
}