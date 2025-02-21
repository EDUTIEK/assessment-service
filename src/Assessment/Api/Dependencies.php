<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Apps\RestContext;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;
use Edutiek\AssessmentService\Assessment\Data\Repositories;

interface Dependencies
{
    public function systemApi(): SystemApi;
    public function repositories(): Repositories;
    public function restContext(): RestContext;
}
