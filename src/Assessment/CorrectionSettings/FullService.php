<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\CorrectionSettings;

use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings;

interface FullService extends ReadService
{
    public function validate(CorrectionSettings $settings): bool;
    public function save(CorrectionSettings $settings): void;
}