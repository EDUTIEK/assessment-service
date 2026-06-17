<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\FileUsage;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\File\FileUsageFinder;

readonly class Finder implements FileUsageFinder
{
    public function __construct(
        private Repositories $repos
    ) {
    }

    public function usedIds(): array
    {
        return array_merge(
            $this->repos->exportFile()->allFileIds()
        );
    }
}
