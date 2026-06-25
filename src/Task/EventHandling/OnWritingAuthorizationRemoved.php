<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\EventHandling;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;
use Edutiek\AssessmentService\System\EventHandling\Events\WritingAuthorizationRemoved;
use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as AssignmentsService;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Assessment\Api\ForTasks;
use Edutiek\AssessmentService\Assessment\Data\NotificationType;

/**
 * Handle a change of writing content
 * - remove a pre-grading from the corrections
 */
readonly class OnWritingAuthorizationRemoved implements Handler
{
    public static function events(): array
    {
        return [WritingAuthorizationRemoved::class];
    }

    public function __construct(
        private int $user_id,
        private AssignmentsService $assignments,
        private Repositories $repos,
    ) {
    }

    /**
     * @param WritingAuthorizationRemoved $event
     */
    public function handle(Event $event): void
    {
        foreach ($this->assignments->allByTaskIdAndWriterId($event->getTaskId(), $event->getWriterId()) as $assignment) {
            $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
                $assignment->getTaskId(),
                $assignment->getWriterId(),
                $assignment->getCorrectorId()
            );

            // remove a pre-grading
            // authorization should already be removed
            if ($summary?->isStarted() && $summary->getGradingStatus() !== GradingStatus::NOT_STARTED) {
                $summary->setGradingStatus(GradingStatus::OPEN, $this->user_id);
                $this->repos->correctorSummary()->save($summary);
            }
        }
    }
}
