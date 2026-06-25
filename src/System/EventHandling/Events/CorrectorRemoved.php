<?php

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event is raised when a corrector is removed from an assessment
 *
 * - remove preferences, templates, snippets and criteria of the corrector
 * - remove assignments of the corrector (triggers AssignmentRemoved)
 */
readonly class CorrectorRemoved implements Event
{
    public function __construct(
        private int $corrector_id,
        private int $ass_id,
    ) {
    }

    public function getCorrectorId(): int
    {
        return $this->corrector_id;
    }

    public function getAssId(): int
    {
        return $this->ass_id;
    }
}
