<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

interface Repository
{
    public function readConfig(): Config;
    public function writeConfig(Config $config) : void;
}
