<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\Events\AssignmentRemoved;
use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\File\Storage;

/**
 * Handle the removal of a correction assignment
 * - remove stored marked PDFs
 */
readonly class OnAssignmentRemoved implements Handler
{
    public static function events(): array
    {
        return [AssignmentRemoved::class];
    }

    public function __construct(
        private Repositories $repos,
        private Storage $storage
    ) {
    }

    /**
     * @param AssignmentRemoved $event
     */
    public function handle(Event $event): void
    {
        $marked = $this->repos->markedPdf()->oneByIds($event->getTaskId(), $event->getWriterId(), $event->getCorrectorId());
        if ($marked) {
            $this->storage->deleteFile($marked->getOwnPdf());
            $this->storage->deleteFile($marked->getSumPdf());
            $this->repos->markedPdf()->delete($marked->getId());
        }
    }
}
