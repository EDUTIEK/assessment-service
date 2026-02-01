<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Properties;

use Edutiek\AssessmentService\Assessment\Data\Properties;

interface ReadService
{
    public function get(): Properties;
}
