<?php

namespace Edutiek\AssessmentService\System\Config;

interface FullService extends ReadService
{
    public function writeConfig(Config $config): void;
}