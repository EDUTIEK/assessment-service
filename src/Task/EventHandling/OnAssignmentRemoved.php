<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\Events\AssignmentRemoved;
use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\WriterRemoved;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as AssignmentsService;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\System\File\Storage;

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
        $this->repos->correctorPoints()->deleteByTaskIdAndWriterIdAndCorrectorId(
            $event->getTaskId(),
            $event->getWriterId(),
            $event->getCorrectorId()
        );

        $this->repos->correctorComment()->deleteByTaskIdAndWriterIdAndCorrectorId(
            $event->getTaskId(),
            $event->getWriterId(),
            $event->getCorrectorId()
        );

        $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
            $event->getTaskId(),
            $event->getWriterId(),
            $event->getCorrectorId()
        );

        if ($summary) {
            $this->storage->deleteFile($summary->getSummaryPdf());
            $this->repos->correctorSummary()->delete($summary->getId());
        }
    }
}
