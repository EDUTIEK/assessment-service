<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\CorrectionSettings;

use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;
use Edutiek\AssessmentService\System\Data\Result;

interface FullService extends ReadService
{
    /**
     * todo: currently unused
     */
    public function validate(CorrectionSettings $settings): Result;
    public function save(CorrectionSettings $settings): void;
}