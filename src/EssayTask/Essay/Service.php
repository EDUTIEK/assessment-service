<?php

namespace Edutiek\AssessmentService\EssayTask\Essay;

use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\EssayTask\Api\ApiException;
use Edutiek\AssessmentService\EssayTask\Data\Essay;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\Task\Manager\ReadService as TasksReadService;

readonly class Service implements FullService
{
    public function __construct(
        private Repositories $repos,
        private WriterReadService $writer_service,
        private TasksReadService $tasks
    ) {
    }

    public function allByWriterId(int $writer_id): array
    {
        $this->checkWriterScope($writer_id);
        return $this->repos->essay()->allByWriterId($writer_id);
    }

    public function allByTaskId(int $task_id): array
    {
        $this->checkTaskScope($task_id);
        return $this->repos->essay()->allByTaskId($task_id);
    }

    public function getByWriterId(int $writer_id): array
    {
        $essays = [];
        foreach ($this->tasks->all() as $task_info) {
            $essays[$task_info->getId()] =
               $this->getByWriterIdAndTaskId($writer_id, $task_info->getId());
        }
        return $essays;
    }

    public function getByWriterIdAndTaskId(int $writer_id, int $task_id): Essay
    {
        $this->checkWriterScope($writer_id);
        $this->checkTaskScope($task_id);
        $essay = $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
        if ($essay === null) {
            $essay = $this->repos->essay()->new()
                ->setWriterId($writer_id)
                ->setTaskId($task_id);
            $this->repos->essay()->save($essay);
        }
        return $essay;
    }

    public function oneByWriterIdAndTaskId(int $writer_id, int $task_id): ?Essay
    {
        $this->checkWriterScope($writer_id);
        $this->checkTaskScope($task_id);
        return $this->repos->essay()->oneByWriterIdAndTaskId($writer_id, $task_id);
    }

    public function new(int $writer_id, int $task_id): Essay
    {
        $this->checkWriterScope($writer_id);
        $this->checkTaskScope($task_id);
        $essay = $this->repos->essay()->new()->setWriterId($writer_id)->setTaskId($task_id);
        return $essay;
    }

    public function save(Essay $essay)
    {
        $this->checkWriterScope($essay->getWriterId());
        $this->checkTaskScope($essay->getTaskId());
        $this->repos->essay()->save($essay);
    }

    /**
     * Check if the writer belongs to the assessment
     */
    private function checkWriterScope(int $writer_id)
    {
        if (!$this->writer_service->has($writer_id)) {
            throw new ApiException("wrong writer_id", ApiException::ID_SCOPE);
        }
    }

    /**
     * Check if the task belongs to the assessment
     */
    private function checkTaskScope(int $task_id)
    {
        if (!$this->tasks->has($task_id)) {
            throw new ApiException("wrong task_id", ApiException::ID_SCOPE);
        }
    }
}
