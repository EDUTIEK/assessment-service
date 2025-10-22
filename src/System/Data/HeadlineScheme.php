<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

enum HeadlineScheme: string
{
    case SINGLE = 'single';
    case THREE = 'three';
    case NUMERIC = 'numeric';
    case EDUTIEK = 'edutiek';
}
