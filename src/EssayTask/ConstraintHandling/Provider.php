<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\ConstraintHandling;

use Edutiek\AssessmentService\EssayTask\Api\Internal;
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
