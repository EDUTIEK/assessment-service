<?php

namespace Edutiek\AssessmentService\Task\ConstraintHandling;

use Edutiek\AssessmentService\Task\Api\Internal;
use Edutiek\AssessmentService\System\ConstraintHandling\AbstractProvider;

class Provider extends AbstractProvider
{
    public function __construct(
        int $ass_id,
        int $user_id,
        private readonly Internal $internal
    ) {
    }
}
