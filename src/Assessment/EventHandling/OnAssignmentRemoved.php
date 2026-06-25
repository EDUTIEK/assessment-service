<?php

namespace Edutiek\AssessmentService\Assessment\EventHandling;

use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\AssignmentRemoved;
use Edutiek\AssessmentService\System\EventHandling\Handler;

/**
 * Handle the removal of a correction assignment
 * - change the correction status to OPEN or STITCH based on the removed assignment
 */
readonly class OnAssignmentRemoved implements Handler
{
    public static function events(): array
    {
        return [AssignmentRemoved::class];
    }

    public function __construct(
        private int $user_id,
        private WriterService $writer_service
    ) {
    }

    /**
     * @param AssignmentRemoved $event
     */
    public function handle(Event $event): void
    {
        if ($event->wasAuthorized()) {
            $writer = $this->writer_service->oneByWriterId($event->getWriterId());
            if ($writer !== null) {
                $this->writer_service->changeCorrectionStatus(
                    $writer,
                    $event->wasStitch() ? CorrectionStatus::STITCH : CorrectionStatus::OPEN,
                    $this->user_id
                );
            }
        }
    }
}
