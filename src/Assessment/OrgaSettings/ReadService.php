<?php

namespace Edutiek\AssessmentService\Assessment\OrgaSettings;

use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;

interface ReadService
{
    public function get(): OrgaSettings;

    /**
     * Get if a review of corrections is possible (user independent)
     */
    public function reviewPossible(): bool;
}
