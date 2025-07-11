<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum WritingStatus : int
{
    case EXCLUDED = 1;
    case AUTHORIZED = 2;
    case STARTED = 3;
    case NOT_STARTED = 4;
}
