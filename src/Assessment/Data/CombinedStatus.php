<?php

namespace Edutiek\AssessmentService\Assessment\Data;

enum CombinedStatus: int
{
    //Numbers match WritingStatus

    case WRITING_EXCLUDED = 1;
    case WRITING_AUTHORIZED = 2;
    case WRITING_STARTED = 3;
    case WRITING_NOT_STARTED = 4;

    case OPEN = 5;
    case STITCH_NEEDED = 6;
    case FINALIZED = 7;
    case APPROXIMATION = 8;
    case CONSULTING = 9;

    public function langVar()
    {
        return match ($this) {
            self::WRITING_EXCLUDED => 'correction_status_writing_excluded',
            self::WRITING_AUTHORIZED => 'correction_status_writing_authorized',
            self::WRITING_STARTED => 'correction_status_writing_started',
            self::WRITING_NOT_STARTED => 'correction_status_writing_not_started',
            self::OPEN => 'correction_status_open',
            self::STITCH_NEEDED => 'correction_status_stitch',
            self::FINALIZED => 'correction_status_finalized',
            self::APPROXIMATION => 'correction_status_approximation',
            self::CONSULTING => 'correction_status_consulting',
        };
    }
}
