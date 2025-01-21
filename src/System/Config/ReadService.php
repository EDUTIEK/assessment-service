<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Config;

use Edutiek\AssessmentService\System\Data\Config;
use Edutiek\AssessmentService\System\Data\Setup;

interface ReadService
{
    /**
     * Get the changeable configuration of the services
     */
    public function getConfig(): Config;

    /**
     * Get the fixed setup of the services in the hosting system
     */
    public function getSetup(): Setup;

    /**
     * Get the effective URL to open a frontend web app
     * This may be taken from the config or built from the setup and the module name
     */
    public function getFrontendUrl(FrontendModule $module): string;
}
