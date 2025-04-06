<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

enum ResourceType: string
{
    case FILE = 'file';
    case URL = 'url';
    case INSTRUCTIONS = 'instructions';
    case SOLUTION = 'solution';
}
