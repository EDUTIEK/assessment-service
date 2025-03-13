<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum OrgaSettingsError: string
{
    case LATEST_END_BEFORE_EARLIEST_START = 'latest_end_before_earliest_start';
    case TIME_LIMIT_TOO_LONG = 'time_limit_too_long';
    case TIME_EXCEEDS_SOLUTION_AVAILABILITY = 'time_exceeds_solution_availability';
}
