<?php

namespace Edutiek\AssessmentService\System\Config;

interface Repository
{
    public function readConfig(): Config;
    public function writeConfig(Config $config) : void;
}