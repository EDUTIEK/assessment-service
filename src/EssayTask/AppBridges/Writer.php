<?php

namespace Edutiek\AssessmentService\EssayTask\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\WriterBridge;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\File\Storage;

class Writer implements WriterBridge
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private Storage $storage,
    ) {
    }

    public function getData(): array
    {
        return [];
    }

    public function getUpdate(): array
    {
        return [];
    }
}
