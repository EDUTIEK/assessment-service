<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\Data;

enum CorrectionSettingsError: string
{
    case MAX_ONE_CORRECTOR = 'max_one_corrector';
}
