<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\ConstraintHandling\Provider;
use Edutiek\AssessmentService\System\ConstraintHandling\ProviderFactory;

class ForConstraints implements ProviderFactory
{
    private array $instances = [];

    public function __construct(
        private readonly Internal $internal
    ) {
    }


    public function Provider(int $ass_id, int $user_id): Provider
    {
        return $this->instances[Provider::class] ??= new Provider(
            $ass_id,
            $user_id,
            $this->internal
        );
    }
}
