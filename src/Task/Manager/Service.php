<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\Task\Manager;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Manager;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskInfo;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskType;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\Task\Data\Repositories as Repositories;

readonly class Service implements Manager
{
    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private Storage $storage,
    ) {
    }

    public function count() : int
    {
        return $this->repos->settings()->countByAssId($this->ass_id);
    }

    public function all() : array
    {
        $infos = [];
        foreach ($this->repos->settings()->allByAssId($this->ass_id) as $setting) {
            $infos[] = $setting->getInfo();
        }
        return $infos;
    }

    public function create(TaskInfo $info) : int
    {
        if ($this->repos->settings()->one($info->getId() ?? 0) !== null) {
            return $info->getId();
        }

        $settings = $this->repos->settings()->new()
            ->setAssId($this->ass_id)
            ->setTitle($info->getTitle())
            ->setTaskType($info->getTaskType())
            ->setPosition($info->getPosition());

        $this->repos->settings()->save($settings);

        // todo: call the creation of the task type

        return $settings->getTaskId();
    }

    public function delete(int $task_id) : void
    {
        $this->repos->settings()->delete($task_id);
        $this->repos->correctorAssignment()->deleteByTaskId($task_id);
        $this->repos->writerComment()->deleteByTaskId($task_id);

        foreach ($this->repos->resource()->allByTaskId($task_id) as $resource) {
            if ($resource->getFileId() !== null) {
                $this->storage->deleteFile($resource->getFileId());
            }
            $this->repos->resource()->delete($resource->getId());
        }

        // todo: delete the data of the task type
    }

    public function clone(int $task_id, int $new_ass_id) : void
    {
        $settings = $this->repos->settings()->one($task_id) ?? $this->repos->settings()->new();
        $this->repos->settings()->save($settings->setTaskId(0)->setAssId($new_ass_id));
        $new_task_id = $settings->getTaskId();

        foreach ($this->repos->resource()->allByTaskId($task_id) as $resource) {
            $new_file_id = null;
            if ($resource->getFileId() !== null) {
                $new_file_id = $this->storage->saveFile(
                    $this->storage->getFileStream($resource->getFileId()),
                    $this->storage->getFileInfo($resource->getFileId())->setId(null)
                )->getId();

            }
            $this->repos->resource()->save($resource
                ->setId(0)
                ->setTaskId($new_task_id)
                ->setFileId($new_file_id));
        }

        // todo: clone the data of the task type
    }
}