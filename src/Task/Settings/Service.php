<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Task\Settings;

use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\Settings;

readonly class Service implements FullService
{
    public function __construct(
        private int $ass_id,
        private int $task_id,
        private Repositories $repos
    ) {
    }

    public function get() : Settings
    {
        return $this->repos->settings()->one($this->ass_id) ??
            $this->repos->settings()->new()
                ->setAssId($this->ass_id)
                ->setTaskId($this->task_id);
    }

    public function validate(Settings $settings) : bool
    {
        $this->checkScope($settings);
        return true;
    }

    public function save(Settings $settings) : void
    {
        $this->checkScope($settings);
        $this->repos->settings()->save($settings);
    }

    private function checkScope(Settings $settings)
    {
        if ($settings->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
        if ($settings->getTaskId() !== $this->task_id) {
            throw new ApiException("wrong task_id", ApiException::ID_SCOPE);
        }
    }
}