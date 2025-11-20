<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\EventHandling;

use Edutiek\AssessmentService\System\EventHandling\Handler;
use Edutiek\AssessmentService\System\EventHandling\Event;
use Edutiek\AssessmentService\System\EventHandling\Events\WritingContentChanged;
use Edutiek\AssessmentService\Task\CorrectorComment\FullService as CommentService;
use Edutiek\AssessmentService\Task\CorrectorSummary\FullService as SummaryService;

readonly class OnWritingContentChanged implements Handler
{
    public static function events(): array
    {
        return [WritingContentChanged::class];
    }

    public function __construct(
        private CommentService $comment_service,
        private SummaryService $summary_service
    ) {
    }

    /**
     * @param WritingContentChanged $event
     */
    public function handle(Event $event): void
    {
        // todo: delete comments and points for writer and task
        // todo: delete points and authorization in summary (should write log entry, if authorization changed)
    }
}
