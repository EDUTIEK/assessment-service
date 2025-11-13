<?php

namespace Edutiek\AssessmentService\Task\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorReadService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as AssignmentService;
use Edutiek\AssessmentService\Task\Data\Repositories as Repositories;
use Edutiek\AssessmentService\Task\Data\Resource;
use Edutiek\AssessmentService\Task\Data\ResourceAvailability;
use Edutiek\AssessmentService\Task\Data\ResourceType;
use Edutiek\AssessmentService\Task\Data\Settings;
use Edutiek\AssessmentService\Task\Data\WriterAnnotation;
use ILIAS\Plugin\LongEssayAssessment\Assessment\Data\Writer;

class CorrectorBridge implements AppBridge
{
    private ?Corrector $corrector;
    /** @var Settings */
    private array $tasks = [];
    /** @var Resource[] */
    private array $resources = [];

    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Repositories $repos,
        private Storage $storage,
        private EntityFullService $entity,
        private CorrectorReadService $corrector_service,
        private WriterReadService $writer_service,
        private AssignmentService $assignment_service,
        private Language $language
    ) {

        $this->corrector = $this->corrector_service->oneByUserId($this->user_id);
        foreach ($this->repos->settings()->allByAssId($this->ass_id) as $task) {
            $this->tasks[$task->getTaskId()] = $task;
            foreach ($this->repos->resource()->allByTaskId($task->getTaskId()) as $resource) {
                $this->resources[$resource->getId()] = $resource;
            }
        }
    }

    public function getData($for_update): array
    {
        $settings = $this->repos->correctionSettings()->one($this->ass_id) ??
            $this->repos->correctionSettings()->new()->setAssId($this->ass_id);

        $data['Settings'] = $this->entity->arrayToPrimitives([
            'positive_rating' => $settings->getPositiveRating(),
            'negative_rating' => $settings->getNegativeRating(),
            'enable_comments' => $settings->getEnableComments(),
            'enable_comment_ratings' => $settings->getEnableCommentRatings(),
            'enable_partial_points' => $settings->getEnablePartialPoints(),
            'enable_summary_pdf' => $settings->getEnableSummaryPdf(),
            'summary_pdf_advice' => $settings->getSummaryPdfAdvice(),
        ]);

        $data['Tasks'] = [];
        foreach ($this->tasks as $task) {
            $data['Tasks'][] = $this->entity->arrayToPrimitives([
                'task_id' => $task->getTaskId(),
                'position' => $task->getPosition(),
                'type' => $task->getTaskType(),
                'title' => $task->getTitle(),
                'instructions' => $task->getInstructions(),
                'solution' => $task->getSolution(),
            ]);
        }

        /** @var Writer[] $writers */
        $writers = [];
        foreach ($this->writer_service->all() as $writer) {
            $writers[$writer->getId()] = $writer;
        }

        $data['Items'] = [];
        if ($this->corrector !== null) {
            $assignments = $this->assignment_service->allByCorrectorIdFiltered($this->corrector->getId());
        } else {
            $assignments = $this->assignment_service->all();
        }
        foreach ($assignments as $assignment) {
            $writer = $writers[$assignment->getWriterId()] ?? null;
            if ($writer?->isAuthorized()) {
                $data['Items'] = $this->entity->arrayToPrimitives([
                    'task_id' => $assignment->getTaskId(),
                    'writer_id' => $writer->getId(),
                    'title' => $writer->getPseudonym() . ' | ' . $this->tasks[$assignment->getTaskId()]->getTitle(),
                    'status' => $writer->getCorrectionStatus()->value
                ]);
            }
        }

        $data['Resources'] = [];
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

        $data['Criteria'] = [];
        foreach ($this->tasks as $task) {
            foreach ($this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($task->getId(), null) as $criterion) {
                $data['Criteria'][] = $this->entity->arrayToPrimitives([
                    'id' => $criterion->getId(),
                    'task_id' => $criterion->getTaskId(),
                    'corrector_id' => $criterion->getCorrectorId(),
                    'title' => $criterion->getTitle(),
                    'description' => $criterion->getDescription(),
                    'points' => $criterion->getPoints(),
                    'is_general' => $criterion->getGeneral(),
                ]);
            }
        }

        $preferences = $this->repos->correctorPrefs()->one($this->corrector->getId() ?? 0)
            ?? $this->repos->correctorPrefs()->new();

        $data['Preferences'] = $this->entity->arrayToPrimitives([
            'essay_page_zoom' => $preferences->getEssayPageZoom(),
            'essay_text_zoom' => $preferences->getEssayTextZoom(),
            'summary_text_zoom' => $preferences->getSummaryTextZoom(),
        ]);

        $data['Snippets'] = [];
        foreach ($this->repos->correctorSnippets()->allByCorrectorId(
            $this->ass_id,
            $this->corrector?->getId() ?? 0
        ) as $snippet) {
            $data['Snippets'][] = $this->entity->arrayToPrimitives([
                'id' => $snippet->getId(),
                'key' => $snippet->getKey(),
                'purpose' => $snippet->getPurpose(),
                'title' => $snippet->getTitle(),
                'text' => $snippet->getText()
            ]);
        }

        return $data;
    }

    public function getFileId(string $entity, int $entity_id): ?string
    {
        $resource = $this->resources[$entity_id] ?? null;
        return $resource?->getFileId();
    }

    public function applyChange(ChangeRequest $change): ChangeResponse
    {
        if ($this->corrector !== null) {
            switch ($change->getType()) {
            }
        }
        return $change->toResponse(false, 'change type not found');
    }
}
