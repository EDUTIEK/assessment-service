<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Assessment\OrgaSettings;

use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;

interface FullService
{
    public function get(): OrgaSettings;
    public function validate(OrgaSettings $settings): bool;
    public function save(OrgaSettings $settings): void;
}
