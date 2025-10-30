<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\System\EventHandling\ObserverFactory;
use Edutiek\AssessmentService\EssayTask\EventHandling\Observer;

readonly class ForEvents implements ObserverFactory
{
    public function __construct(
        private Internal $internal
    ) {
    }

    public function observer(int $ass_id, int $user_id): Observer
    {
        return $this->internal->eventObserver($ass_id, $user_id);
    }
}
