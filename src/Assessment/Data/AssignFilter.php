<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum AssignFilter: string
{
    case ALL = "all";
    case CORRECTABLE = "correctable";
}
