<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\ConstraintHandling\Provider;
use Edutiek\AssessmentService\System\ConstraintHandling\ProviderFactory;

readonly class ForConstraints implements ProviderFactory
{
    public function __construct(
        private Internal $internal
    ) {
    }


    public function provider(int $ass_id, int $user_id): Provider
    {
        return $this->internal->constraintProvider($ass_id, $user_id);
    }
}
