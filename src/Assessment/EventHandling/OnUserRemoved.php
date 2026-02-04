<?php

namespace Edutiek\AssessmentService\Assessment\EventHandling;

use Edutiek\AssessmentService\Assessment\Api\Internal;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\UserRemoved;
use Edutiek\AssessmentService\System\EventHandling\Handler;

readonly class OnUserRemoved implements Handler
{
    public static function events(): array
    {
        return [UserRemoved::class];
    }

    public function __construct(
        private int $user_id,
        private Repositories $repos,
        private Internal $internal
    ) {
    }

    /**
     * @param UserRemoved $event
     */
    public function handle(Event $event): void
    {
        foreach ($this->repos->writer()->allByUserId($event->getUserId()) as $writer) {
            $this->internal->writer($writer->getAssId(), $this->user_id)->remove($writer);
        }

        foreach ($this->repos->corrector()->allByUserId($event->getUserId()) as $corrector) {
            $this->internal->corrector($corrector->getAssId(), $this->user_id)->remove($corrector);
        }
    }
}
