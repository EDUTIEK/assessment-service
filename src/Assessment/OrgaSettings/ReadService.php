<?php

namespace Edutiek\AssessmentService\Assessment\OrgaSettings;

use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;

interface ReadService
{
    public function get(): OrgaSettings;
}