<?php

namespace Edutiek\AssessmentService\Assessment\EventHandling;

use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\WriterRemoved;
use Edutiek\AssessmentService\System\EventHandling\Handler;

readonly class OnWriterRemoved implements Handler
{
    public static function events(): array
    {
        return [WriterRemoved::class];
    }

    public function __construct(
        private Repositories $repos
    ) {
    }

    /**
     * @param WriterRemoved $event
     */
    public function handle(Event $event): void
    {
        foreach ($this->repos->alert()->allByAssIdAndWriterId(
            $event->getAssId(),
            $event->getWriterId()
        ) as $alert
        ) {
            $this->repos->alert()->delete($alert->getId());
        }
    }
}
