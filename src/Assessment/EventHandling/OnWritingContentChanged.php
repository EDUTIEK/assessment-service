<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\WritingContentChanged;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;

readonly class OnWritingContentChanged implements Handler
{
    public static function events(): array
    {
        return [WritingContentChanged::class];
    }

    public function __construct(
        private int $user_id,
        private WriterService $writer_service
    ) {
    }

    /**
     * @param WritingContentChanged $event
     */
    public function handle(Event $event): void
    {
        $writer = $this->writer_service->oneByWriterId($event->getWriterId());
        $this->writer_service->removeCorrectionFinalisation($writer, $this->user_id);
        $this->writer_service->removeWritingAuthorization($writer);
    }
}
