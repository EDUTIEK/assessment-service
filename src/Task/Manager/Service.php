<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Manager;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Manager;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskInfo;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Data\Repositories as Repositories;
use Edutiek\AssessmentService\Task\Data\Settings;
use Edutiek\AssessmentService\Task\TypeInterfaces\ApiFactory as TypeApiFactory;
use Edutiek\AssessmentService\System\Language\FullService as Language;

readonly class Service implements Manager
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private Storage $storage,
        private TypeApiFactory $types,
        private Language $language
    ) {
    }

    public function count(): int
    {
        return $this->repos->settings()->countByAssId($this->ass_id);
    }

    /** @return TaskInfo[] */
    public function all(): array
    {
        $infos = [];
        foreach ($this->repos->settings()->allByAssId($this->ass_id) as $setting) {
            $infos[] = $setting->getInfo();
        }
        return $infos;
    }

    public function one(int $task_id): ?TaskInfo
    {
        $settings = $this->repos->settings()->one($task_id);
        if ($settings === null) {
            return null;
        }
        $this->checkScope($settings);
        return $settings->getInfo();
    }

    public function first(): ?TaskInfo
    {
        foreach ($this->repos->settings()->allByAssId($this->ass_id) as $setting) {
            return $setting->getInfo();
        }
        return null;
    }

    public function create(TaskInfo $info): int
    {
        $max_pos = null;
        foreach ($this->repos->settings()->allByAssId($this->ass_id) as $setting) {
            $max_pos = max($max_pos, $setting->getPosition());
        }

        $settings = $this->repos->settings()->new()
            ->setAssId($this->ass_id)
            ->setTitle($info->getTitle())
            ->setTaskType($info->getTaskType())
            ->setPosition($max_pos ? $max_pos + 1 : 0);

        $this->repos->settings()->save($settings);

        $this->types->api($info->getTaskType())
            ->manager($this->ass_id, $settings->getTaskId(), $this->user_id)
            ->create();

        if ($this->repos->correctionSettings()->one($this->ass_id ?? 0) === null) {
            $this->repos->correctionSettings()->save(
                $this->repos->correctionSettings()->new()
                            ->setAssId($this->ass_id)
                            ->setPositiveRating($this->language->txt('comment_rating_positive_default'))
                            ->setNegativeRating($this->language->txt('comment_rating_negative_default'))
            );
        }

        return $settings->getTaskId();
    }

    public function delete(int $task_id): void
    {
        $settings = $this->repos->settings()->one($task_id);
        if ($settings === null) {
            return;
        }
        $this->checkScope($settings);

        $task_type = $settings->getTaskType();

        $this->repos->settings()->delete($task_id);
        $this->repos->correctionSettings()->delete($this->ass_id);
        $this->repos->correctorAssignment()->deleteByTaskId($task_id);
        $this->repos->writerComment()->deleteByTaskId($task_id);
        $this->repos->correctorSummary()->deleteByTaskId($task_id);
        $this->repos->correctorComment()->deleteByTaskId($task_id);
        $this->repos->correctorPoints()->deleteByTaskId($task_id);

        foreach ($this->repos->resource()->allByTaskId($task_id) as $resource) {
            if ($resource->getFileId() !== null) {
                $this->storage->deleteFile($resource->getFileId());
            }
            $this->repos->resource()->delete($resource->getId());
        }

        if ($task_type !== null) {
            $this->types->api($task_type)
                ->manager($this->ass_id, $task_id, $this->user_id)
                ->delete();
        }
    }

    public function clone(int $task_id, int $new_ass_id): void
    {
        $settings = $this->repos->settings()->one($task_id);
        if ($settings === null) {
            return;
        }
        $this->checkScope($settings);

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

        if ($this->repos->correctionSettings()->one($new_ass_id) === null) {
            $this->repos->correctionSettings()->save($this->repos->correctionSettings()->one($this->ass_id)
                                                                 ->setAssId($new_ass_id));
        }

        $this->types->api($settings->getTaskType())
            ->manager($this->ass_id, $task_id, $this->user_id)
            ->clone($new_ass_id, $new_task_id);
    }

    private function checkScope(Settings $settings)
    {
        if ($settings->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}
