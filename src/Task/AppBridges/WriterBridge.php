<?php

namespace Edutiek\AssessmentService\Task\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\WriterBridge as WriterBridgeInterface;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Task\Data\Repositories as Repositories;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\Task\Data\Resource;
use Edutiek\AssessmentService\Task\Data\ResourceAvailability;
use Edutiek\AssessmentService\Task\Data\ResourceType;

class WriterBridge implements WriterBridgeInterface
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private Storage $storage,
        private EntityFullService $entity,
        private Language $language
    ) {
    }
    public function getData(): array
    {
        $data = [];

        foreach ($this->repos->settings()->allByAssId($this->ass_id) as $task) {
            $data['Tasks'][] = $this->entity->arrayToPrimitives([
                'id' => $task->getTaskId(),
                'position' => $task->getPosition(),
                'type' => $task->getTaskType(),
                'title' => $task->getTitle(),
                'instructions' => $task->getInstructions(),
            ]);

            /** @var Resource $resource */
            foreach ($this->repos->resource()->allByTaskId($task->getTaskId()) as $resource) {

                $source = null;
                $mimetype = null;
                $size = null;
                $title = null;

                if ($resource->getAvailability() !== ResourceAvailability::AFTER) {
                    if ($resource->getType() == ResourceType::URL) {
                        $source = $resource->getUrl();
                        $title = $resource->getTitle();
                    } elseif ($resource->getFileId() !== null) {
                        $info = $this->storage->getFileInfo($resource->getFileId());
                        $source = $info->getFileName();
                        $mimetype = $info->getMimetype();
                        $size = $info->getSize();
                        $title = $resource->getTitle();
                    }
                    if ($resource->getType() == ResourceType::INSTRUCTIONS) {
                        $title = $this->language->txt('task_instructions');
                    }
                    if ($resource->getType() == ResourceType::SOLUTION) {
                        $title = $this->language->txt('tasksolution');
                    }

                    $data['Resources'][] = $this->entity->arrayToPrimitives([
                        'id' => $resource->getId(),
                        'type' => $resource->getType(),
                        'embedded' => $resource->getEmbedded(),
                        'source' => $source,
                        'mimetype' => $mimetype,
                        'size' => $size,
                        'title' => $title
                    ]);
                }
            }
        }


        return $data;
    }

    public function getUpdate(): array
    {
        return [];
    }
}
