<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\BackgroundTask;

interface ComponentJob
{
    /**
     * The background task should offer a download to the user when finished
     * The id of the file to be downloaded is returned by run()
     */
    public static function withDownload(): bool;


    /**
     * The created file should be deleted when the download is no longer needed
     */
    public static function allowDelete(): bool;

    /**
     * Run a job
     * @param mixed[] $args    list of scalar arguments
     * @return ?string  storage id of a file that can be downloaded
     */
    public function run($args): ?string;
}
