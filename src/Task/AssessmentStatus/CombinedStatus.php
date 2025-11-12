<?php

namespace Edutiek\AssessmentService\Task\AssessmentStatus;

enum CombinedStatus : int
{
    //Numbers match WritingStatus

    case WRITING_EXCLUDED = 1;
    case WRITING_AUTHORIZED = 2;
    case WRITING_STARTED = 3;
    case WRITING_NOT_STARTED = 4;

    case STARTED = 5;
    case STITCH_NEEDED = 6;
    case FINALIZED = 7;
    case APPROXIMATION = 8;
    case CONSULTING = 9;

}