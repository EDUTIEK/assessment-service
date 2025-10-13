<?php

namespace Edutiek\AssessmentService\Task\WriterBridge;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\WriterBridge;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Task\Data\Repositories as Repositories;

class Service implements WriterBridge
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private Storage $storage,
        private Language $language
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
