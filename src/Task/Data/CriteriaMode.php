<?php

namespace Edutiek\AssessmentService\Task\Data;

enum CriteriaMode: string
{
    case NONE = 'none';
    case FIXED = 'fixed';
    case CORRECTOR = 'corr';
}
