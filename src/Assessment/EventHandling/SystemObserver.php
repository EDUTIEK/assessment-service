<?php

namespace Edutiek\AssessmentService\Assessment\EventHandling;

use Edutiek\AssessmentService\Assessment\Api\Internal;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\System\EventHandling\AbstractObserver;

class SystemObserver extends AbstractObserver
{
    public function __construct(
        int $user_id,
        Internal $internal,
        Repositories $repos,
    ) {
        $this->registerHandler(OnUserRemoved::class, fn() => new OnUserRemoved(
            $user_id,
            $repos,
            $internal
        ));
    }
}
