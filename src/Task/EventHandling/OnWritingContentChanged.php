<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\EventHandling;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\WritingContentChanged;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as AssignmentsService;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Api\ForTasks;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;

/**
 * Handle a change of writing content
 * - remove a pre-grading from the corrections
 * - notify the correctors if they already have correction content
 */
readonly class OnWritingContentChanged implements Handler
{
    public static function events(): array
    {
        return [WritingContentChanged::class];
    }

    public function __construct(
        private AssignmentsService $assignments,
        private Repositories $repos,
        private ForTasks $assessment_api,
        private Storage $storage,
    ) {
    }

    /**
     * @param WritingContentChanged $event
     */
    public function handle(Event $event): void
    {
        foreach ($this->assignments->allByTaskIdAndWriterId($event->getTaskId(), $event->getWriterId()) as $assignment) {
            $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
                $assignment->getTaskId(),
                $assignment->getWriterId(),
                $assignment->getCorrectorId()
            );

            if ($summary?->isStarted()
                || $this->repos->correctorComment()->hasByTaskIdAndWriterIdAndCorrectorId(
                    $assignment->getTaskId(),
                    $assignment->getWriterId(),
                    $assignment->getCorrectorId()
                )
                || $this->repos->correctorPoints()->hasByTaskIdAndWriterIdAndCorrectorId(
                    $assignment->getTaskId(),
                    $assignment->getWriterId(),
                    $assignment->getCorrectorId()
                )
            ) {
                $this->assessment_api->notification()->createFor(
                    NotificationType::CORRECTOR_WRITING_CHANGED,
                    $this->assessment_api->writer()->oneByWriterId($assignment->getWriterId()),
                    $this->assessment_api->corrector()->oneById($assignment->getCorrectorId())
                );
            }

            $this->repos->correctorComment()->deleteByTaskIdAndWriterId($event->getTaskId(), $event->getWriterId());
            $this->repos->correctorPoints()->deleteByTaskIdAndWriterId($event->getTaskId(), $event->getWriterId());

            if ($summary) {
                $this->storage->deleteFile($summary->getSummaryPdf());
                $this->repos->correctorSummary()->delete($summary->getId());
            }
        }
    }
}
