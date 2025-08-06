<?php

namespace Edutiek\AssessmentService\EssayTask\Essay;

use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\EssayTask\Api\ApiException;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;

readonly class Service implements FullService
{
    public function __construct(
        private Repositories $repos,
        private WriterReadService $writer_service
    ) {
    }

    /**
     * @inheritDoc
     */
    public function allByWriterId(int $writer_id) : array
    {
        if (!$this->writer_service->has($writer_id)) {
            throw new ApiException("wrong writer_id", ApiException::ID_SCOPE);
        }
        return $this->repos->essay()->allByWriterId($writer_id);
    }

    public function allByTaskId(int $task_id) : array {
        return $this->repos->essay()->allByTaskId($task_id);
    }

    public function oneByWriterIdAndTaskId(int $writer_id, int $task_id)
    {
        return $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
    }

    public function new(int $writer_id, int $task_id) : Essay
    {
        $essay = $this->repos->essay()->new()->setWriterId($writer_id)->setTaskId($task_id);
        $this->checkScope($essay);
        return $essay;
    }

    public function save(Essay $essay)
    {
        $this->checkScope($essay);
        $this->repos->essay()->save($essay);
    }

    /**
     * todo: also check if the task_id belongs to the assessment
     */
    private function checkScope(Essay $essay)
    {
        if (!$this->writer_service->has($essay->getWriterId())) {
            throw new ApiException("wrong writer_id", ApiException::ID_SCOPE);
        }
    }
}