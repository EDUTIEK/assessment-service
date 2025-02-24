<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum ResultAvailableType: string
{
    case FINALISED = 'finalised';
    case REVIEW = 'review';
    case DATE = 'date';
}
