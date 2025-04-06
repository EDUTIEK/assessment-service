<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

enum ResourceAvailability: string
{
    case BEFORE = 'before';
    case DURING = 'during';
    case AFTER = 'after';
}
