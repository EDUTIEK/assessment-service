<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Data;

interface SetupRepo
{
    /**
     * Get setup data from the hosting system
     */
    public function get(): Setup;

}