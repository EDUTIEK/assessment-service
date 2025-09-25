<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\EventHandling;

interface ObserverFactory
{
    public function observer(int $ass_id, int $user_id): Observer;
}
