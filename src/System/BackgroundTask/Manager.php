<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\BackgroundTask;

interface Manager
{
    /**
     * @param class-string<Job> $job
     */
    public function run(string $title, string $job, ...$args): void;
}
