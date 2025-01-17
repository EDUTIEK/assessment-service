<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Data\Config;

interface FullService extends ReadService
{
    public function writeConfig(Config $config): void;
}
