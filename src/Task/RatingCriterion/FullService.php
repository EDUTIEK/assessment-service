<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\RatingCriterion;

use Edutiek\AssessmentService\Task\Data\RatingCriterion;

interface FullService
{
    /**
     * Get all criteria assigned to a specific corrector
     * Null means general criteria
     * @return RatingCriterion[]
     */
    public function allByCorrectorId(?int $corrector_id): array;

    /**
     * Get all criteria that can be used by a corrector
     * This depends on the correction settings and may be general or individual criteria
     * The criteria are sorted by title
     * @return RatingCriterion[]
     */
    public function allForCorrector(int $corrector_id): array;

    public function one(int $criterion_id): ?RatingCriterion;
    public function new(): RatingCriterion;
    public function save(RatingCriterion $criterion);
    public function delete(RatingCriterion $criterion);
}
