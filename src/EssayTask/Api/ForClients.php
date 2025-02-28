<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $task_id,
        private readonly Dependencies $dependencies
    ) {
    }

}
