<?php

namespace Edutiek\AssessmentService\Task\Data;

enum GradingStatus: string
{
    case NOT_STARTED = "not_started";
    case OPEN = "open";
    case PRE_GRADED = "pre_graded";
    case AUTHORIZED = "authorized";
    case REVISED = "revised";
}
