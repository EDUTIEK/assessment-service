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
}
