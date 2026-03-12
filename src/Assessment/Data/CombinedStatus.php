<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum CombinedStatus: int
{
    //Numbers match WritingStatus

    case WRITING_EXCLUDED = 1;
    case WRITING_AUTHORIZED = 2;        // is overwritten by OPEN
    case WRITING_STARTED = 3;
    case WRITING_NOT_STARTED = 4;

    case OPEN = 5;
    case STITCH_NEEDED = 6;
    case FINALIZED = 7;
    case APPROXIMATION = 8;
    case CONSULTING = 9;
}
