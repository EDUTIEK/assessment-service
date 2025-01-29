<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

interface ConfigRepo
{
    /**
     * Get the global configuration of the assessment-service
     */
    public function getConfig(): Config;

    /**
     * Set the global configuration of the assessment-service
     */
    public function saveConfig(Config $config) : void;

    /**
     * Get setup data from the hosting system
     */
    public function getSetup(): Setup;
}
