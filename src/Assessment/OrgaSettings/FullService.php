<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\OrgaSettings;

use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\System\Data\Result;

interface FullService extends ReadService
{
    public function validate(OrgaSettings $settings): Result;
    public function save(OrgaSettings $settings): void;
}
