<?php

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Data\Config;

interface ReadService
{
    public function readConfig(): Config;
}