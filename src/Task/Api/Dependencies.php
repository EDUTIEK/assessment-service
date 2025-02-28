<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;
use Edutiek\AssessmentService\Task\TypeInterfaces\ApiFactory as TypeApiFactory;

interface Dependencies
{
    public function systemApi(): SystemApi;
    public function typeApis(): TypeApiFactory;
    public function repositories(): Repositories;
}
