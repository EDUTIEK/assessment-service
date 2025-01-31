<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

interface ConfigRepo
{
    /**
     * Get the global configuration of the assessment-service
     */
    public function get(): Config;

    /**
     * Set the global configuration of the assessment-service
     */
    public function save(Config $config) : void;
}
