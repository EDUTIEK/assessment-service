<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\TaskInterfaces;

enum GradingStatus: string
{
    case NOT_STARTED = "not_started";
    case OPEN = "open";
    case PRE_GRADED = "pre_graded";
    case AUTHORIZED = "authorized";
    case REVISED = "revised";

    public function position()
    {
        return match ($this) {
            GradingStatus::NOT_STARTED => 0,
            GradingStatus::OPEN => 1,
            GradingStatus::PRE_GRADED => 2,
            GradingStatus::AUTHORIZED => 3,
            GradingStatus::REVISED => 4,
        };
    }

    /**
     * Get the order of two grading status
     * @return int -1 if status1 < status2, 0 if status1 = status2, 1 if status1 > status2
     */
    public static function order(GradingStatus $status1, GradingStatus $status2)
    {
        return $status1->position() <=> $status2->position();
    }
}
