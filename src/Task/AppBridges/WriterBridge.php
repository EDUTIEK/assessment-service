<?php

namespace Edutiek\AssessmentService\Task\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Task\Data\Repositories as Repositories;
use Edutiek\AssessmentService\Task\Data\ResourceAvailability;
use Edutiek\AssessmentService\Task\Data\ResourceType;
use Edutiek\AssessmentService\Task\Data\WriterAnnotation;
use ILIAS\Plugin\LongEssayAssessment\Assessment\Data\Writer;

class WriterBridge implements AppBridge
{
    private const CHANGE_TYPE_ANNOTATION = 'anno';

    private ?Writer $writer;
    private $tasks = [];
    private $resources = [];

    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private Storage $storage,
        private EntityFullService $entity,
        private HtmlProcessing $html,
        private WriterReadService $writer_service,
        private Language $language
    ) {

        $this->writer = $this->writer_service->oneByUserId($this->user_id);
        foreach ($this->repos->settings()->allByAssId($this->ass_id) as $task) {
            $this->tasks[$task->getTaskId()] = $task;
            foreach ($this->repos->resource()->allByTaskId($task->getTaskId()) as $resource) {
                $this->resources[$resource->getId()] = $resource;
            }
        }
    }

    public function getData($for_update): array
    {
        if ($this->writer === null || $for_update) {
            return [];
        }

        $data = [
            'Tasks' => [],
            'Resources' => [],
            'Annotations' => []
        ];

        foreach ($this->tasks as $task) {
            $data['Tasks'][] = $this->entity->arrayToPrimitives([
                'task_id' => $task->getTaskId(),
                'position' => $task->getPosition(),
                'type' => $task->getTaskType(),
                'title' => $task->getTitle(),
                'instructions' => $this->html->processHtmlForMarking((string) $task->getInstructions()),
            ]);
        }

        foreach ($this->resources as $resource) {
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

        foreach ($this->repos->writerAnnotation()->allByWriterId($this->writer->getId()) as $annotation) {
            $data['Annotations'][] = $this->entity->arrayToPrimitives([
                'id' => $annotation->getId(),
                'task_id' => $annotation->getTaskId(),
                'resource_id' => $annotation->getResourceId(),
                'mark_key' => $annotation->getMarkKey(),
                'mark_value' => $annotation->getMarkValue(),
                'comment' => $annotation->getComment(),
                'parent_number' => $annotation->getParentNumber(),
                'start_position' => $annotation->getStartPosition(),
                'end_position' => $annotation->getEndPosition()
            ]);
        }

        return $data;
    }

    public function getFileId(string $entity, int $entity_id): ?string
    {
        $resource = $this->resources[$entity_id] ?? null;
        if ($resource !== null && $resource->getAvailability() !== ResourceAvailability::AFTER) {
            return $resource->getFileId();
        }
        return null;
    }

    public function applyChanges(string $type, array $changes): array
    {
        if ($this->writer !== null) {
            switch ($type) {
                case self::CHANGE_TYPE_ANNOTATION:
                    return array_map(fn(ChangeRequest $change) => $this->applyAnnotation($change), $changes);
                default:
                    return array_map(fn(ChangeRequest $change) => $change->toResponse(false, 'wrong type'), $changes);
            }
        }
        return array_map(fn(ChangeRequest $change) => $change->toResponse(false, 'writer not found'), $changes);
    }


    private function applyAnnotation(ChangeRequest $change): ChangeResponse
    {
        $repo = $this->repos->writerAnnotation();

        $annotation = $repo->new();
        $data = $change->getPayload();

        $this->entity->fromPrimitives([
            'id' => $data['id'] ?? null,
            'task_id' => $data['task_id'] ?? null,
            'writer_id' => $this->writer->getId(),
            'resource_id' => $data['resource_id'] ?? null,
            'mark_key' => $data['mark_key'] ?? null,
            'mark_value' => $data['mark_value'] ?? null,
            'comment' => $data['comment'] ?? null,
            'parent_number' => $data['parent_number'] ?? null,
            'start_position' => $data['start_position'] ?? null,
            'end_position' => $data['end_position'] ?? null,
        ], $annotation, WriterAnnotation::class);

        $this->entity->secure($annotation, WriterAnnotation::class);

        $found = $repo->oneByResourceIdAndMarkKey($annotation->getResourceId(), $annotation->getMarkKey());
        if ($found !== null && (
            $found->getWriterId() !== $this->writer->getId()
                || $found->getTaskId() !== 0 && !isset($this->tasks[$found->getTaskId()])
        )) {
            return $change->toResponse(false, 'wrong scope');
        }

        switch ($change->getAction()) {
            case ChangeAction::SAVE:
                if ($found !== null && $found->getTaskId() !== $annotation->getTaskId()) {
                    return $change->toResponse(false, 'task reference changed');
                }
                $repo->save($annotation);
                return $change->toResponse(true);

            case ChangeAction::DELETE:
                if ($found) {
                    $repo->delete($found->getId());
                }
                return $change->toResponse(true);
        }

        return $change->toResponse(false, 'wrong action');
    }
}
