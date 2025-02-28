<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Manager;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;

readonly class Service implements \Edutiek\AssessmentService\Task\TypeInterfaces\Manager
{
    public function __construct(
        private int $task_id,
        private Repositories $repos,
        private Storage $storage,
        private Language $language
    ) {
    }

    public function create(): void
    {
        // TODO: Implement create() method.
    }

    public function delete(): void
    {
        // TODO: Implement delete() method.
    }

    public function clone(int $new_task_id): void
    {
        // TODO: Implement clone() method.
    }
}
