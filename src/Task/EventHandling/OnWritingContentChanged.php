<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\WritingContentChanged;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as AssignmentsService;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Api\ForTasks;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorService;
use Edutiek\AssessmentService\Assessment\Notification\DeliverService as NotificationService;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;

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
    ) {
    }

    /**
     * @param WritingContentChanged $event
     */
    public function handle(Event $event): void
    {
        foreach ($this->assignments->allByTaskIdAndWriterId($event->getTaskId(), $event->getWriterId()) as $assignment) {
            if ($this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
                $assignment->getTaskId(),
                $assignment->getWriterId(),
                $assignment->getCorrectorId()
            )?->isStarted()
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
        }
    }
}
