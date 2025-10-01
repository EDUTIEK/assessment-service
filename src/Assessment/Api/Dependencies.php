<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Apps\RestContext;
use Edutiek\AssessmentService\Assessment\Data\Repositories;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\Api as TaskApi;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;
use Edutiek\AssessmentService\System\ConstraintHandling\Collector;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;

interface Dependencies
{
    public function systemApi(): SystemApi;
    public function taskApi(): TaskApi;
    public function eventDispatcher(int $ass_id, int $user_id): Dispatcher;
    public function constraintCollector(int $ass_id, int $user_id): Collector;
    public function repositories(): Repositories;
    public function restContext(): RestContext;
}
