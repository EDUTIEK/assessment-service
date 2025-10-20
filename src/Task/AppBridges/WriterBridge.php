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
                'task_id' => $task->getTaskId(),
                'position' => $task->getPosition(),
                'type' => $task->getTaskType(),
                'title' => $task->getTitle(),
                'instructions' => $task->getInstructions(),
            ]);

            /** @var Resource $resource */
            foreach ($this->repos->resource()->allByTaskId($task->getTaskId()) as $resource) {

                if ($resource->getAvailability() !== ResourceAvailability::AFTER) {
                    $info = $this->storage->getFileInfo($resource->getFileId());
                    $title = match ($resource->getType()) {
                        ResourceType::INSTRUCTIONS => $this->language->txt('task_instructions'),
                        ResourceType::SOLUTION => $this->language->txt('task_solution'),
                        default => $resource->getTitle(),
                    };

                    $data['Resources'][] = $this->entity->arrayToPrimitives([
                        'id' => $resource->getId(),
                        'task_id' => $resource->getTaskId(),
                        'type' => $resource->getType(),
                        'embedded' => $resource->getEmbedded(),
                        'source' => $info?->getFileName() ?? $resource->getUrl(),
                        'mimetype' => $info?->getMimetype(),
                        'size' => $info?->getMimetype(),
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

    public function getFileId(string $entity, int $entity_id): ?string
    {
        $resource = $this->repos->resource()->one($entity_id);
        if ($resource != null
            && $this->repos->settings()->has($this->ass_id, $resource->getTaskId())
            && $resource->getAvailability() !== ResourceAvailability::AFTER
        ) {
            return $resource->getFileId();
        }
        return null;
    }
}
