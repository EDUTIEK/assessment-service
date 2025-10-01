<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\Assessment\Api\ForTasks as AssessmentApi;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;
use Edutiek\AssessmentService\System\ConstraintHandling\Collector;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;
use Edutiek\AssessmentService\Task\Api\ForTypes as TaskApi;

interface Dependencies
{
    public function systemApi(): SystemApi;
    public function assessmentApi(int $ass_id, int $user_id): AssessmentApi;
    public function taskApi(int $ass_id, int $user_id): TaskApi;
    public function eventDispatcher(int $ass_id, int $user_id): Dispatcher;
    public function constraintCollector(int $ass_id, int $user_id): Collector;
    public function repositories(): Repositories;
}
