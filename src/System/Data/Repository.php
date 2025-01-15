<?php

namespace Edutiek\AssessmentService\System\Data;

interface Repository
{
    public function readConfig(): Config;
    public function writeConfig(Config $config) : void;
}