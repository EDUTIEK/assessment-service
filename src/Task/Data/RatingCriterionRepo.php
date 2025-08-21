<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

interface RatingCriterionRepo
{
    public function new(): RatingCriterion;
    public function one(int $id): ?RatingCriterion;

    /**
     * Get common or individual rating criteria
     * check for common criteria  with corrector_id IS NULL if $corrector_id == null
     * @return RatingCriterion[]
     */
    public function allByTaskIdAndCorrectorId(int $task_id, ?int $corrector_id): array;
    public function save(RatingCriterion $entity): void;
    public function delete(int $id): void;
    public function deleteByTaskId(int $task_id): void;
}