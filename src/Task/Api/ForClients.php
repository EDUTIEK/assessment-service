<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly Dependencies $dependencies
    ) {
    }

}
