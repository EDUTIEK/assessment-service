<?php

namespace Edutiek\AssessmentService\Task\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\AppBridge;
use Edutiek\AssessmentService\Assessment\Apps\AppCorrectorBridge;
use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as AssessmentSettingsService;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorReadService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\HtmlProcessing\FullService as HtmlProcessing;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as AssignmentService;
use Edutiek\AssessmentService\Task\CorrectionProcess\Service as CorrectionProcessService;
use Edutiek\AssessmentService\Task\Data\Repositories as Repositories;
use Edutiek\AssessmentService\Task\Data\Resource;
use Edutiek\AssessmentService\Task\Data\ResourceAvailability;
use Edutiek\AssessmentService\Task\Data\ResourceType;
use Edutiek\AssessmentService\Task\Data\Settings;
use Edutiek\AssessmentService\Task\Data\WriterAnnotation;
use ILIAS\Plugin\LongEssayAssessment\Assessment\Data\Writer;

class CorrectorBridge implements AppCorrectorBridge
{
    private ?Corrector $corrector;
    /** @var Settings */
    private array $tasks = [];
    /** @var Resource[] */
    private array $resources = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly Repositories $repos,
        private readonly Storage $storage,
        private readonly EntityFullService $entity,
        private readonly CorrectorReadService $corrector_service,
        private readonly WriterReadService $writer_service,
        private readonly AssessmentSettingsService $assesment_settings,
        private readonly AssignmentService $assignment_service,
        private readonly CorrectionProcessService $process_service,
        private readonly Language $language,
        private readonly UserReadService $user_service,
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
        $data = [];

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

        $task_ids = [];
        foreach ($assignments as $assignment) {
            $writer = $writers[$assignment->getWriterId()] ?? null;
            if ($writer?->isAuthorized()) {
                $data['Items'][] = $this->entity->arrayToPrimitives([
                    'task_id' => $assignment->getTaskId(),
                    'writer_id' => $writer->getId(),
                    'position' => $assignment->getPosition()->value,
                    'title' => $writer->getPseudonym(),
                    'correction_status' => $writer->getCorrectionStatus()->value,
                    'correction_allowed' => $this->process_service->canCorrect($assignment),
                    'authorization_allowed' => $this->process_service->canAuthorize($assignment),
                    'revision_allowed' => $this->process_service->canRevise($assignment),
                ]);
            }
            $task_ids[] = $assignment->getTaskId();
        }
        $task_ids = array_unique($task_ids);

        $data['Tasks'] = [];
        foreach ($task_ids as $task_id) {
            $task = $this->tasks[$task_id] ?? null;
            $data['Tasks'][] = $this->entity->arrayToPrimitives([
                'task_id' => $task->getTaskId(),
                'position' => $task->getPosition(),
                'type' => $task->getTaskType(),
                'title' => $task->getTitle(),
                'instructions' => $task->getInstructions(),
                'solution' => $task->getSolution(),
            ]);
        }

        $data['Resources'] = [];
        foreach ($this->resources as $resource) {
            if (in_array($resource->getTaskId(), $task_ids)) {
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

    public function getItem(int $task_id, int $writer_id): ?array
    {
        $data = [
            'Item' => [],
            'Correctors' => [],
            'Criteria' => [],
            'Summaries' => [],
            'Comments' => [],
            'Points' => []
        ];

        $assignment = $this->assignment_service->oneByIds($writer_id, (int) $this->corrector?->getId(), $task_id);

        $writer = $this->writer_service->oneByWriterId($writer_id);
        if ($writer?->isAuthorized()) {
            $data['Item'] = $this->entity->arrayToPrimitives([
                'task_id' => $task_id,
                'writer_id' => $writer_id,
                'position' => $assignment?->getPosition()?->value,
                'title' => $writer->getPseudonym(),
                'correction_status' => $writer->getCorrectionStatus()->value,
                'correction_allowed' => isset($assignment) && $this->process_service->canCorrect($assignment),
                'authorization_allowed' => isset($assignment) && $this->process_service->canAuthorize($assignment),
                'revision_allowed' => isset($assignment) && $this->process_service->canRevise($assignment),
            ]);
        } else {
            return [];
        }

        $settings = $this->assesment_settings->get();
        $task_criteria_loaded = [];
        foreach ($this->assignment_service->allByTaskIdAndWriterId($task_id, $writer_id) as $assignment) {
            if ($this->corrector === null || $this->corrector->getId() === $assignment->getCorrectorId() || $settings->getMutualVisibility()) {
                $corrector = $this->corrector_service->oneById($assignment->getCorrectorId());
                if ($corrector) {
                    $user = $this->user_service->getUser($corrector->getUserId());

                    $data['Correctors'][] = $this->entity->arrayToPrimitives([
                       'id' => $corrector->getId(),
                       'corrector_id' => $corrector->getId(),
                       'title' => $user?->getFullname(false)
                           ?? $this->language->txt($assignment->getPosition()->languageVariable()),
                       'initials' => $user->getInitials() ?? $this->language->txt($assignment->getPosition()->initialsLanguageVariable()),
                       'position' => $assignment->getPosition()->value,
                    ]);

                    $summary = $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
                        $assignment->getTaskId(),
                        $assignment->getWriterId(),
                        $assignment->getCorrectorId()
                    );
                    if ($summary?->getCorrectorId() === $this->corrector?->getId() || $summary?->isAuthorized()) {
                        $add_details = true;
                    } else {
                        $add_details = false;
                        $summary = $this->repos->correctorSummary()->new()->setCorrectorId($corrector->getId());
                    }

                    $data['Summaries'][] = $this->entity->arrayToPrimitives([
                        'task_id' => $assignment->getTaskId(),
                        'writer_id' => $assignment->getWriterId(),
                        'corrector_id' => $assignment->getCorrectorId(),

                        'text' => $summary->getSummaryText(),
                        'points' => $summary->getPoints(),
                        'pdf' => $summary->getSummaryPdf(),
                        'status' => $summary->getGradingStatus(),
                        'revision_text' => $summary->getRevisionText(),
                        'revision_points' => $summary->getRevisionPoints(),
                        'last_change' => $summary->getLastChange(),
                    ]);

                    if ($add_details) {
                        // criteria of the corrector
                        $criteria = $this->repos->ratingCriterion()->allByTaskIdAndCorrectorId(
                            $assignment->getTaskId(),
                            $assignment->getCorrectorId()
                        );
                        // general criteria
                        if (empty($task_criteria_loaded[$assignment->getTaskId()])) {
                            $criteria = array_merge(
                                $criteria,
                                $this->repos->ratingCriterion()->allByTaskIdAndCorrectorId(
                                    $assignment->getTaskId(),
                                    null
                                )
                            );
                            $task_criteria_loaded[$assignment->getTaskId()] = true;
                        }

                        foreach ($criteria as $criterion) {
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

                        $comments = $this->repos->correctorComment()->allByTaskIdAndWriterIdAndCorrectorId(
                            $assignment->getTaskId(),
                            $assignment->getWriterId(),
                            $assignment->getCorrectorId()
                        );
                        foreach ($comments as $comment) {
                            $data['Comments'][] = $this->entity->arrayToPrimitives([
                                'id' => $comment->getId(),
                                'task_id' => $assignment->getTaskId(),
                                'writer_id' => $assignment->getWriterId(),
                                'corrector_id' => $comment->getCorrectorId(),
                                'start_position' => $comment->getStartPosition(),
                                'end_position' => $comment->getEndPosition(),
                                'parent_number' => $comment->getParentNumber(),
                                'comment' => $comment->getComment(),
                                'rating' => $comment->getRating(),
                                'marks' => $comment->getMarks(),
                            ]);
                        }

                        $points = $this->repos->correctorPoints()->allByTaskIdAndWriterIdAndCorrectorId(
                            $assignment->getTaskId(),
                            $assignment->getWriterId(),
                            $assignment->getCorrectorId()
                        );
                        foreach ($points as $point) {
                            $data['Points'][] = $this->entity->arrayToPrimitives([
                                'id' => $point->getId(),
                                'task_id' => $assignment->getTaskId(),
                                'writer_id' => $assignment->getWriterId(),
                                'corrector_id' => $point->getCorrectorId(),
                                'comment_id' => $point->getCommentId(),
                                'criterion_id' => $point->getCriterionId(),
                                'points' => $point->getPoints(),
                            ]);
                        }
                    }
                }
            }
        }

        return $data;
    }

    public function getFileId(string $entity, int $entity_id): ?string
    {
        switch ($entity) {
            case 'image':
            case 'thumb':
                // todo
                return null;

            case 'resource':
                $resource = $this->resources[$entity_id] ?? null;
                return $resource?->getFileId();
        }
        return null;
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
