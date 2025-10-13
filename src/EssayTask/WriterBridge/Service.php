<?php

namespace Edutiek\AssessmentService\EssayTask\WriterBridge;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\WriterBridge;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\File\Storage;

class Service implements WriterBridge
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private Storage $storage,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getUpdate(): array
    {
        return [];
    }
}
