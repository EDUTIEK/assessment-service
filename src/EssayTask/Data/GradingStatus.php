<?php

namespace Edutiek\AssessmentService\EssayTask\Data;

enum GradingStatus: string
{
    case NOT_STARTED = "not_started";
    case OPEN = "open";
    case AUTHORIZED = "authorized";
}
