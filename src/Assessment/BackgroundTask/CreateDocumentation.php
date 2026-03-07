<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\BackgroundTask;

use Edutiek\AssessmentService\System\BackgroundTask\ComponentJob;
use Edutiek\AssessmentService\System\File\Storage as Storage;
use Edutiek\AssessmentService\Assessment\Export\DocumentationExport;
use Edutiek\AssessmentService\Assessment\Data\WritingTask;

readonly class CreateDocumentation implements ComponentJob
{
    public function __construct(
        private DocumentationExport $documentation
    ) {
    }

    public static function withDownload(): bool
    {
        return true;
    }

    public static function allowDelete(): bool
    {
        return false;
    }

    public function run($args): ?string
    {
        return $this->documentation->create();
    }
}
