<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\CorrectionSettings;

use Edutiek\AssessmentService\EssayTask\Data\CorrectionSettings;

interface FullService
{
    public function get(): CorrectionSettings;
    public function save(CorrectionSettings $settings): void;
}