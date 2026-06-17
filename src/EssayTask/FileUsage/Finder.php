<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\FileUsage;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
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
            $this->repos->essay()->allFileIds(),
            $this->repos->essayImage()->allFileIds()
        );
    }
}
