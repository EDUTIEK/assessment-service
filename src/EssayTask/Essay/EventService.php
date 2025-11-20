<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Essay;

use Edutiek\AssessmentService\EssayTask\Data\Essay;

interface EventService
{
    public function delete(Essay $essay): void;
}
