<?php

namespace Edutiek\AssessmentService\Task\Data;

enum SummaryInclusion: int
{
    case INCLUDE_NOT = 0;
    case ICLUDE_INFO = 1;
    case INCLUDE_RELEVANT = 2;

}
