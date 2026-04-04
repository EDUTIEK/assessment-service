<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

readonly class ForCron
{
    public function __construct(
        private int $user_id,
        private Internal $internal
    ) {
    }

    public function reviewNotifications(): CronHandler
    {
        return $this->internal->reviewNotificationHandler($this->user_id);
    }
}
