<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum PdfFeedbackMode: string
{
    case SIDE_LANDSCAPE = "side-by-side";     // landscape
    case SIDE_PORTRAIT = "side-portrait";   // portrait
    case SEQUENCE = "sequence";
}
