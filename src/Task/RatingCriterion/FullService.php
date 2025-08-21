<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Task\RatingCriterion;

use Edutiek\AssessmentService\Task\Data\RatingCriterion;

interface FullService
{
    /** @return RatingCriterion[] */
    public function allByCorrectorId(?int $corrector_id): array;
    public function one(int $criterion_id): ?RatingCriterion;
    public function new(): RatingCriterion;
    public function save(RatingCriterion $criterion);
    public function delete(RatingCriterion $criterion);
}