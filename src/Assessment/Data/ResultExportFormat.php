<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum ResultExportFormat: string
{
    case EXAMIS = "examis";
    case JUSTA = "justa";
    case EDUTIEK = "edutiek";
}
