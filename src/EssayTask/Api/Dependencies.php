<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\System\Api\ForServices as SystemApi;
use Edutiek\AssessmentService\Task\Api\ForTypes as TaskApi;
use Edutiek\AssessmentService\Event\EventDispatcher;

interface Dependencies
{
    public function systemApi(): SystemApi;
    public function taskApi(int $ass_id): TaskApi;
    public function repositories(): Repositories;
    public function eventManager(): EventDispatcher;
}
