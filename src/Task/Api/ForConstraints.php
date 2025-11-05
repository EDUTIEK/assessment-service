<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\System\ConstraintHandling\ProviderFactory;
use Edutiek\AssessmentService\Task\ConstraintHandling\Provider;

readonly class ForConstraints implements ProviderFactory
{
    public function __construct(
        private Internal $internal
    ) {
    }


    public function Provider(int $ass_id, int $user_id): Provider
    {
        return $this->internal->constraintProvider($ass_id, $user_id);
    }
}
