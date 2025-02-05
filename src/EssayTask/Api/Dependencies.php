<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;

interface Dependencies
{
    public function systemApi(): SystemApi;
    public function repositories(): Repositories;
}
