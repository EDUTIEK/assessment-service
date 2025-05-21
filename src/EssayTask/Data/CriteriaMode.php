<?php

namespace Edutiek\AssessmentService\EssayTask\Data;

enum CriteriaMode: string
{
    case NONE = 'none';
    case FIXED = 'fixed';
    case CORRECTOR = 'corr';
}
