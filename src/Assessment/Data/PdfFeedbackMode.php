<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum PdfFeedbackMode: string
{
    case SIDE_BY_SIDE = "side-by-side";
    case SEQUENCE = "sequence";
}
