<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\BackgroundTask;

interface ComponentJob
{
    /**
     * The background task should offer a download to the user when finished
     */
    public static function withDownload(): bool;


    /**
     * Allow the deletion of a created file by the user
     */
    public static function allowDelete(): bool;

    /**
     * Run a job
     * @param mixed[] $args    list of scalar arguments
     * @return ?string  storage id of a file that can be downloaded
     */
    public function run($args): ?string;
}
