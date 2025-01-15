<?php

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Api\Dependencies;
use Edutiek\AssessmentService\System\Data\Config;

readonly class Service implements ReadService, FullService
{
    public function __construct(private Dependencies $dependencies) {}

    public function readConfig(): Config
    {
        return $this->dependencies->configRepo()->readConfig();
    }

    public function writeConfig(Config $config) : void
    {
        $this->dependencies->configRepo()->writeConfig($config);
    }
}