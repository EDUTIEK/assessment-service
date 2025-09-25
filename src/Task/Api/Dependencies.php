<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;
use Edutiek\AssessmentService\Task\TypeInterfaces\ApiFactory as TypeApiFactory;
use Edutiek\AssessmentService\Assessment\Api\ForTasks as AssessmentApiFactory;
use Edutiek\AssessmentService\System\EventHandling\Dispatcher;

interface Dependencies
{
    public function systemApi(): SystemApi;
    public function typeApis(): TypeApiFactory;
    public function assessmentApis(int $ass_id, int $user_id): AssessmentApiFactory;
    public function eventDispatcher(int $ass_id, int $user_id): Dispatcher;
    public function repositories(): Repositories;
}
