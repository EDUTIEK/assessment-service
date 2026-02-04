<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\CorrectorRemoved;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as AssignmentsService;
use Edutiek\AssessmentService\Task\Data\Repositories;

readonly class OnCorrectorRemoved implements Handler
{
    public static function events(): array
    {
        return [CorrectorRemoved::class];
    }

    public function __construct(
        private AssignmentsService $assignments,
        private Repositories $repos
    ) {
    }

    /**
     * @param CorrectorRemoved $event
     */
    public function handle(Event $event): void
    {
        $this->repos->correctorTaskPrefs()->deleteByCorrectorId($event->getCorrectorId());
        $this->repos->correctorSnippets()->deleteByCorrectorId($event->getCorrectorId());
        $this->repos->ratingCriterion()->deleteByCorrectorId($event->getCorrectorId());

        foreach ($this->assignments->allByCorrectorId($event->getCorrectorId()) as $assignment) {
            // this triggers an AssignmentRemoved event to delete all assigned correction data
            $this->assignments->removeAssignment($assignment);
        }
    }
}
