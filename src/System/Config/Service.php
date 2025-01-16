<?php

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Api\Dependencies;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Data\Repository;

readonly class Service implements ReadService, FullService
{
    public function __construct(
        private Repository $config_repo
    ) {}

    public function readConfig(): Config
    {
        return $this->config_repo->readConfig();
    }

    public function writeConfig(Config $config) : void
    {
        $this->config_repo->writeConfig($config);
    }
}