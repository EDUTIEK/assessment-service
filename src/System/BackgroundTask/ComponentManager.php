<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\BackgroundTask;

interface ComponentManager
{
    /**
     * Create a background task of the component
     * This can be called by the client system or a service component to queue a background task for running
     *
     * @param string $title     Title of the task for display in interactions
     * @param class-string<ComponentJob> $job  Name of the class that runs the job
     * @param mixed[] $args list of scalar arguments for the job
     */
    public function create(string $title, string $job, array $args): void;

    /**
     * Run a background task of the component
     * This is called by the client system when the background task is due to run
     *
     * @param class-string<ComponentJob> $job Name of the class that runs the job
     * @param mixed[] $args list of scalar arguments for the job
     * @return string
     */
    public function run(string $job, array $args): ?string;
}
