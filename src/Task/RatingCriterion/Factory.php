<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\RatingCriterion;

interface Factory
{
    public function ratingCriterion(int $task_id, int $ass_id, int $user_id): FullService;
}
