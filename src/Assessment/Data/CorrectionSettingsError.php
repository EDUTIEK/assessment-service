<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum CorrectionSettingsError: string
{
    case MAX_ONE_CORRECTOR = 'max_one_corrector';
    case ATLEAST_TWO_CORRECTORS = 'atleast_two_correctors';
}
