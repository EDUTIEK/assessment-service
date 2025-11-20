<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\WriterRemoved;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Essay\EventService as EssayService;

readonly class OnWriterRemoved implements Handler
{
    public static function events(): array
    {
        return [WriterRemoved::class];
    }

    public function __construct(
        private Repositories $repos,
        private EssayService $essay_service,
    ) {
    }

    /**
     * @param WriterRemoved $event
     */
    public function handle(Event $event): void
    {
        $this->repos->writerPrefs()->delete($event->getWriterId());

        // use repo, not service to prevent a scope check
        foreach ($this->repos->essay()->allByWriterId($event->getWriterId()) as $essay) {
            $this->essay_service->delete($essay);
        }
    }
}
