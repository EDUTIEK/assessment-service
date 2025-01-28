<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;


class ForRest
{
    protected static array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies
    )
    {
    }

}
