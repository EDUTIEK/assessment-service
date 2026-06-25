<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\WriterRemoved;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as AssignmentsService;
use Edutiek\AssessmentService\Task\Data\Repositories;

/**
 * Handle the removal of a writer from an assessment
 * - delete the writer's annotations
 * - remove the correction assignments of the writer (triggering AssignmentRemoved)
 */
readonly class OnWriterRemoved implements Handler
{
    public static function events(): array
    {
        return [WriterRemoved::class];
    }

    public function __construct(
        private AssignmentsService $assignments,
        private Repositories $repos
    ) {
    }

    /**
     * @param WriterRemoved $event
     */
    public function handle(Event $event): void
    {
        foreach ($this->repos->writerAnnotation()->allByWriterId($event->getWriterId()) as $annotation) {
            $this->repos->writerAnnotation()->delete($annotation->getId());
        }
        foreach ($this->assignments->allByWriterId($event->getWriterId()) as $assignment) {
            // this triggers an AssignmentRemoved event to delete all assigned correction data
            $this->assignments->removeAssignment($assignment);
        }
    }
}
