<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum ResultExportFormat: string
{
    case EXAMIS = "examis";
    case JUSTA = "justa";
    case EDUTIEK = "edutiek";
}
