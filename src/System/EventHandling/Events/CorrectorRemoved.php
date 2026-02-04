<?php

namespace Edutiek\AssessmentService\System\EventHandling\Events;

use Edutiek\AssessmentService\System\EventHandling\Event;

/**
 * This event must be raised when a corrector is removed from an assessment
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
