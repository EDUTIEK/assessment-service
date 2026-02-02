<?php

namespace Edutiek\AssessmentService\EssayTask\EventHandling;

use Edutiek\AssessmentService\EssayTask\Api\Internal;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\EventHandling\AbstractObserver;

class Observer extends AbstractObserver
{
    public function __construct(
        int $ass_id,
        int $user_id,
        Internal $internal,
        Repositories $repos
    ) {

        $this->registerHandler(OnWriterAdded::class, fn() => new OnWriterAdded(
            $repos,
            $internal->essay($ass_id, $user_id, true),
        ));

        $this->registerHandler(OnWriterRemoved::class, fn() => new OnWriterRemoved(
            $repos,
            $internal->essay($ass_id, $user_id, true)
        ));
    }
}
