<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

interface ConfigRepo
{
    public function getConfig(): Config;
    public function saveConfig(Config $config) : void;
    public function getSetup(): Setup;
}
