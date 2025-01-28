<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;


class ForClients
{
    protected static array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $context_id,
        private readonly Dependencies $dependencies
    )
    {
    }


}
