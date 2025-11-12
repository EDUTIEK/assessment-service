<?php

namespace Edutiek\AssessmentService\Task\Data;

enum GradingStatus: string
{
    case NOT_STARTED = "not_started";
    case OPEN = "open";
    case AUTHORIZED = "authorized";
    case PRE_GRADED = "pre_graded";
}
