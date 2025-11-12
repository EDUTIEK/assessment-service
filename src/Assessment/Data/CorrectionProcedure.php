<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum CorrectionProcedure: string
{
    case APPROXIMATION = 'approximation';
    case CONSULTING = 'consulting';
    case NONE = 'none';
}
