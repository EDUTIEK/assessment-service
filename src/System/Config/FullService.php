<?php

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Data\Config;

interface FullService extends ReadService
{
    public function writeConfig(Config $config): void;
}