<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\TaskSettings;

use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Data\TaskSettings;
use Edutiek\AssessmentService\EssayTask\Api\ApiException;

class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $task_id,
        private Repositories $repos
    ) {
    }

    public function get() : TaskSettings
    {
        return $this->repos->taskSettings()->one($this->task_id) ??
            $this->repos->taskSettings()->new()->setAssId($this->task_id)->setTaskId($this->task_id);
    }

    public function save(TaskSettings $settings) : void
    {
        $this->checkScope($settings);
        $this->repos->taskSettings()->save($settings);
    }

    private function checkScope(TaskSettings $settings)
    {
        if ($settings->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
        if ($settings->getTaskId() !== $this->task_id) {
            throw new ApiException("wrong task_id", ApiException::ID_SCOPE);
        }
    }
}