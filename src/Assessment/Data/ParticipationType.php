<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum ParticipationType: string
{
    case FIXED = 'fixed';
    case INSTANT = 'instant';
}
