<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Views\Api;

use Edutiek\AssessmentService\Views\Data\AssessmentViewRepo;
use Edutiek\AssessmentService\Views\Api\Dependencies;

readonly class ForRest
{
    public function __construct(
        private Dependencies $dependencies,
    ) {
    }

    public function assessment(): AssessmentViewRepo
    {
        return $this->dependencies->assessment();
    }
}
