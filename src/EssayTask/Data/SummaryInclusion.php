<?php

namespace Edutiek\AssessmentService\EssayTask\Data;

enum SummaryInclusion: int
{
    case INCLUDE_NOT = 0;
    case INCLUDE_INFO = 1;
    case INCLUDE_RELEVANT = 2;
}
