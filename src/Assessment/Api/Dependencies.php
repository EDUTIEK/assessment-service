<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Data\ObjectRepo;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;

interface Dependencies
{
    public function systemApi(): SystemApi;
    public function objectRepo(): ObjectRepo;

}