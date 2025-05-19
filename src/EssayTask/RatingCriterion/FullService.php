<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\RatingCriterion;

use Edutiek\AssessmentService\EssayTask\Data\RatingCriterion;

interface FullService
{
    /** @return RatingCriterion[] */
    public function allByCorrectorId(?int $corrector_id): array;
    public function new(): RatingCriterion;
    public function save(RatingCriterion $criterion);
}