<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Api\Dependencies;
use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Data\Repository;

readonly class Service implements ReadService, FullService
{
    public function __construct(
        private Repository $config_repo
    ) {}

    public function getConfig(): Config
    {
        return $this->config_repo->getConfig();
    }

    public function saveConfig(Config $config) : void
    {
        $this->config_repo->saveConfig($config);
    }
}
