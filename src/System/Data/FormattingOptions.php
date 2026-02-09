<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

enum FormattingOptions: string
{
    case NONE = 'none';
    case MINIMAL = 'minimal';
    case MEDIUM = 'medium';
    case FULL = 'full';
    case EXTENDED = 'extended';
}
