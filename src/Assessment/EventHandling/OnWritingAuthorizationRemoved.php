<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\EventHandling;

use Edutiek\AssessmentService\Assessment\Data\CorrectionStatus;
use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\WritingAuthorizationRemoved;
use Edutiek\AssessmentService\Assessment\Writer\FullService as WriterService;

/**
 * Handle a change of writing content
 * - force a correction status OPEN
 */
readonly class OnWritingAuthorizationRemoved implements Handler
{
    public static function events(): array
    {
        return [WritingAuthorizationRemoved::class];
    }

    public function __construct(
        private int $user_id,
        private WriterService $writer_service
    ) {
    }

    /**
     * @param WritingAuthorizationRemoved $event
     */
    public function handle(Event $event): void
    {
        $writer = $this->writer_service->oneByWriterId($event->getWriterId());

        if ($writer->getCorrectionStatus() !== CorrectionStatus::OPEN) {
            $this->writer_service->changeCorrectionStatus($writer, CorrectionStatus::OPEN, $this->user_id);
        }
    }
}
