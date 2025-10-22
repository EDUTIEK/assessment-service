<?php

namespace Edutiek\AssessmentService\Task\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\WriterBridge as WriterBridgeInterface;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Task\Data\Repositories as Repositories;
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
        private HtmlProcessing $html,
        private Language $language
    ) {
    }

    public function getData(): array
    {
        $data = [
            'Tasks' => [],
            'Resources' => [],
            'Annotations' => []
        ];

        foreach ($this->repos->settings()->allByAssId($this->ass_id) as $task) {
            $data['Tasks'][] = $this->entity->arrayToPrimitives([
                'task_id' => $task->getTaskId(),
                'position' => $task->getPosition(),
                'type' => $task->getTaskType(),
                'title' => $task->getTitle(),
                'instructions' => $this->html->processHtmlForMarking($task->getInstructions()),
            ]);

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

            foreach ($this->repos->writerAnnotation()->allByTaskId($task->getTaskId()) as $annotation) {
                $data['Annotations'][] = $this->entity->arrayToPrimitives([
                    'id' => $annotation->getId(),
                    'task_id' => $annotation->getTaskId(),
                    'writer_id' => $annotation->getWriterId(),
                    'resource_id' => $annotation->getResourceId(),
                    'mark_key' => $annotation->getMarkKey(),
                    'mark_value' => $annotation->getMarkValue(),
                    'comment' => $annotation->getComment(),
                    'parent_number' => $annotation->getParentNumber(),
                    'start_position' => $annotation->getStartPosition(),
                    'end_position' => $annotation->getEndPosition()
                ]);
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
