<?php

namespace Edutiek\AssessmentService\Task\AssessmentStatus;

enum CorrectionStatus : int
{
    //Numbers match WritingStatus

    case WRITING_EXCLUDED = 1;
    case FINALIZED = 7;
    case STITCH_NEEDED = 6;
    case STARTED = 5;
    case WRITING_AUTHORIZED = 2;

    case WRITING_STARTED = 3;
    case WRITING_NOT_STARTED = 4;
}