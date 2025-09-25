<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\EventHandling;

interface DispatcherFactory
{
    public function dispatcher(int $ass_id, int $user_id): Dispatcher;
}
