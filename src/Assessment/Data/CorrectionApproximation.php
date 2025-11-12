<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum CorrectionApproximation: string
{
    case ONE = 'one';
    case BOTH = 'both';
    case DECIDE = 'decide';
}
