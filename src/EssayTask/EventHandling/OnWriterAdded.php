<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Essay\EventService as EssayService;
use Edutiek\AssessmentService\System\EventHandling\Events\WriterAdded;
use Edutiek\AssessmentService\Task\Manager\ReadService as ManagerReadService;

readonly class OnWriterAdded implements Handler
{
    public static function events(): array
    {
        return [WriterAdded::class];
    }

    public function __construct(
        private Repositories $repos,
        private EssayService $essay_service,
    ) {
    }

    /**
     * @param WriterAdded $event
     */
    public function handle(Event $event): void
    {
        $this->essay_service->createAll($event->getWriterId());
    }
}
