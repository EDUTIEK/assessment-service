<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\PdfCreation;

enum PdfPurpose: string
{
    case WRITING = 'writing';
    case CORRECTION = 'correction';
}
