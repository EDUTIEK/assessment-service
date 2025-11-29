<?php

namespace Edutiek\AssessmentService\Task\AppBridges;

use Edutiek\AssessmentService\Assessment\Apps\AppCorrectorBridge;
use Edutiek\AssessmentService\Assessment\Apps\ChangeAction;
use Edutiek\AssessmentService\Assessment\Apps\ChangeRequest;
use Edutiek\AssessmentService\Assessment\Apps\ChangeResponse;
use Edutiek\AssessmentService\Assessment\CorrectionSettings\ReadService as AssessmentSettingsService;
use Edutiek\AssessmentService\Assessment\Corrector\ReadService as CorrectorReadService;
use Edutiek\AssessmentService\Assessment\Data\CorrectionSettings as AssesmentCorrectionSettings;
use Edutiek\AssessmentService\Assessment\Data\Corrector;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\System\Entity\FullService as EntityFullService;
use Edutiek\AssessmentService\System\File\Storage;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\System\User\ReadService as UserReadService;
use Edutiek\AssessmentService\Task\CorrectionProcess\Service as CorrectionProcessService;
use Edutiek\AssessmentService\Task\CorrectionSettings\FullService as CorrectionSettingsService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as AssignmentService;
use Edutiek\AssessmentService\Task\Data\CorrectionSettings as TaskCorrectionSettings;
use Edutiek\AssessmentService\Task\Data\CorrectorAssignment;
use Edutiek\AssessmentService\Task\Data\CorrectorComment;
use Edutiek\AssessmentService\Task\Data\CorrectorPoints;
use Edutiek\AssessmentService\Task\Data\CorrectorSummary;
use Edutiek\AssessmentService\Task\Data\CriteriaMode;
use Edutiek\AssessmentService\Task\Data\RatingCriterion;
use Edutiek\AssessmentService\Task\Data\Repositories as Repositories;
use Edutiek\AssessmentService\Task\Data\Resource;
use Edutiek\AssessmentService\Task\Data\ResourceType;
use Edutiek\AssessmentService\Task\Data\Settings;
use ILIAS\Plugin\LongEssayAssessment\Assessment\Data\Writer;

class CorrectorBridge implements AppCorrectorBridge
{
    private const CHANGE_TYPE_COMMENT = 'comment';
    private const CHANGE_TYPE_POINTS = 'points';
    private const CHANGE_TYPE_SUMMARY = 'summary';
    private const CHANGE_TYPE_PREFERENCES = 'preferences';
    private const CHANGE_TYPE_SNIPPETS = 'snippets';

    private ?Corrector $corrector;
    /** @var Settings */
    private array $tasks = [];
    /** @var Resource[] */
    private array $resources = [];
    private AssesmentCorrectionSettings $assessment_settings;
    private TaskCorrectionSettings $correction_settings;
    /** @var RatingCriterion[][][] task_id => corrector_id => criterion_id => criterion */
    private $criteria;

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly Repositories $repos,
        private readonly Storage $storage,
        private readonly EntityFullService $entity,
        private readonly CorrectorReadService $corrector_service,
        private readonly WriterReadService $writer_service,
        private readonly AssessmentSettingsService $assesment_settings_service,
        private readonly CorrectionSettingsService $correction_settings_service,
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

        $this->assessment_settings = $this->assesment_settings_service->get();
        $this->correction_settings = $this->correction_settings_service->get();
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
                $summary = $this->getSummaryForAssignment($assignment);
                $data['Items'][] = $this->entity->arrayToPrimitives([
                    'task_id' => $assignment->getTaskId(),
                    'writer_id' => $assignment->getWriterId(),
                    'position' => $assignment->getPosition()->value,
                    'pseudonym' => $writer->getPseudonym(),
                    'correction_status' => $writer->getCorrectionStatus()->value,
                    'grading_status' => $summary->getGradingStatus(),
                    'can_correct' => $this->process_service->canCorrect($assignment),
                    'can_authorize' => $this->process_service->canAuthorize($assignment),
                    'can_revise' => $this->process_service->canRevise($assignment),
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
            'Corrector' => $this->entity->toPrimitives($this->corrector, Corrector::class),
            'Item' => [],
            'Corrections' => [],
            'Criteria' => [],
            'Summaries' => [],
            'Comments' => [],
            'Points' => []
        ];

        $assignment = $this->assignment_service->oneByIds($writer_id, (int) $this->corrector?->getId(), $task_id);
        $writer = $this->writer_service->oneByWriterId($writer_id);
        if ($writer?->isAuthorized()) {
            $summary = $this->getSummaryForAssignment($assignment);
            $data['Item'] = $this->entity->arrayToPrimitives([
                'task_id' => $assignment->getTaskId(),
                'writer_id' => $assignment->getWriterId(),
                'position' => $assignment->getPosition()->value,
                'pseudonym' => $writer->getPseudonym(),
                'correction_status' => $writer->getCorrectionStatus()->value,
                'grading_status' => $summary->getGradingStatus(),
                'can_correct' => $this->process_service->canCorrect($assignment),
                'can_authorize' => $this->process_service->canAuthorize($assignment),
                'can_revise' => $this->process_service->canRevise($assignment),
            ]);
        } else {
            return [];
        }

        foreach ($this->assignment_service->allByTaskIdAndWriterId($task_id, $writer_id) as $assignment) {
            if ($this->corrector === null || $this->corrector->getId() === $assignment->getCorrectorId()
                || $this->assessment_settings->getMutualVisibility()) {
                $corrector = $this->corrector_service->oneById($assignment->getCorrectorId());
                if ($corrector) {
                    $user = $this->user_service->getUser($corrector->getUserId());

                    // correctors are loaded per item, so we can add the position here
                    // a corrector with the same corrector id may be sent
                    $data['Corrections'][] = $this->entity->arrayToPrimitives([
                       'task_id' => $assignment->getTaskId(),
                       'writer_id' => $assignment->getWriterId(),
                       'corrector_id' => $assignment->getCorrectorId(),
                       'user_id' => $corrector->getUserId(),
                       'title' => $user?->getFullname(false)
                           ?? $this->language->txt($assignment->getPosition()->languageVariable()),
                       'initials' => $user->getInitials() ?? $this->language->txt($assignment->getPosition()->initialsLanguageVariable()),
                       'position' => $assignment->getPosition()->value,
                    ]);

                    $summary = $this->getSummaryForAssignment($assignment);
                    if ($summary->getCorrectorId() == $this->corrector?->getId() || $summary->isAuthorized()) {
                        $add_details = true;
                    } else {
                        $add_details = false;
                        // this has only the ids set
                        $summary = $this->getNewSummaryForAssignment($assignment);
                    }

                    $data['Summaries'][] = $this->entity->arrayToPrimitives([
                        'task_id' => $summary->getTaskId(),
                        'writer_id' => $summary->getWriterId(),
                        'corrector_id' => $summary->getCorrectorId(),
                        'text' => $summary->getSummaryText(),
                        'points' => $summary->getPoints(),
                        'pdf' => $summary->getSummaryPdf(),
                        'status' => $add_details ? $summary->getGradingStatus() : $summary->getGradingStatusLight(),
                        'revision_text' => $summary->getRevisionText(),
                        'revision_points' => $summary->getRevisionPoints(),
                        'require_other_revision' => $summary->getRequireOtherRevision(),
                        'last_change' => $summary->getLastChange(),
                    ]);

                    if ($add_details) {

                        $criteria = $this->getCriteriaForTaskAndCorrector(
                            $assignment->getTaskId(),
                            $assignment->getCorrectorId()
                        );
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

                        // keys for comments and points are generated in the app
                        // the backend delivers them instead of the integer id
                        // this avoids an update of keys in the app when saving changes

                        $comment_keys = [];
                        foreach ($comments as $comment) {
                            $comment_keys[$comment->getId()] = $comment->getComment();
                            $data['Comments'][] = $this->entity->arrayToPrimitives([
                                'key' => $comment->getKey(),
                                'task_id' => $assignment->getTaskId(),
                                'writer_id' => $assignment->getWriterId(),
                                'corrector_id' => $comment->getCorrectorId(),
                                'start_position' => $comment->getStartPosition(),
                                'end_position' => $comment->getEndPosition(),
                                'parent_number' => $comment->getParentNumber(),
                                'comment' => $comment->getComment(),
                                'rating' => $comment->getRating(),
                                'marks' => $comment->getMarks() ? json_decode($comment->getMarks(), true) : null,
                            ]);
                        }

                        $points = $this->repos->correctorPoints()->allByTaskIdAndWriterIdAndCorrectorId(
                            $assignment->getTaskId(),
                            $assignment->getWriterId(),
                            $assignment->getCorrectorId()
                        );
                        foreach ($points as $point) {
                            $data['Points'][] = $this->entity->arrayToPrimitives([
                                'key' => $point->getId(),
                                'comment_key' => $comment_keys[$point->getCommentId()] ?? null,
                                'task_id' => $assignment->getTaskId(),
                                'writer_id' => $assignment->getWriterId(),
                                'corrector_id' => $point->getCorrectorId(),
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
            case 'resource':
                $resource = $this->resources[$entity_id] ?? null;
                return $resource?->getFileId();
        }
        return null;
    }

    public function applyChanges(string $type, array $changes): array
    {
        if ($this->corrector !== null) {
            switch ($type) {
                case self::CHANGE_TYPE_COMMENT:
                    return array_map(fn(ChangeRequest $change) => $this->applyComment($change), $changes);
                case self::CHANGE_TYPE_POINTS:
                    return array_map(fn(ChangeRequest $change) => $this->applyPoints($change), $changes);
                case self::CHANGE_TYPE_SUMMARY:
                    return array_map(fn(ChangeRequest $change) => $this->applySummary($change), $changes);
                case self::CHANGE_TYPE_PREFERENCES:
                    return array_map(fn(ChangeRequest $change) => $this->applyPreferences($change), $changes);
                case self::CHANGE_TYPE_SNIPPETS:
                    return array_map(fn(ChangeRequest $change) => $this->applySnippets($change), $changes);
                default:
                    return array_map(fn(ChangeRequest $change) => $change->toResponse(false, 'wrong type'), $changes);
            }
        }
        return array_map(fn(ChangeRequest $change) => $change->toResponse(false, 'corrector not found'), $changes);
    }

    private function applyComment(ChangeRequest $change): ChangeResponse
    {
        $repo = $this->repos->correctorComment();

        $comment = $repo->new();
        $data = $change->getPayload();

        $this->entity->fromPrimitives([
            'key' => $change->getKey(),
            'task_id' => $data['task_id'] ?? null,
            'writer_id' => $data['writer_id'] ?? null,
            'corrector_id' => $data['corrector_id'] ?? null,
            'start_position' => $data['start_position'] ?? null,
            'end_position' => $data['end_position'] ?? null,
            'parent_number' => $data['parent_number'] ?? null,
            'comment' => $data['comment'] ?? null,
            'rating' => $data['rating'] ?? null,
            'marks' => ($data['marks'] ?? null) ? json_encode($data['marks']) : null,
        ], $comment, CorrectorComment::class);

        $this->entity->secure($comment, CorrectorComment::class);

        // check scope
        if ($comment->getCorrectorId() !== $this->corrector?->getId()
            || !$this->repos->correctorAssignment()->hasByIds(
                $comment->getWriterId(),
                $comment->getCorrectorId(),
                $comment->getTaskId()
            )) {
            return $change->toResponse(false, 'wrong scope');
        }

        $found = $repo->oneByTaskIdAndWriterIdAndKey($comment->getTaskId(), $comment->getWriterId(), $comment->getKey());
        switch ($change->getAction()) {
            case ChangeAction::SAVE:
                if ($found) {
                    $comment->setId($found->getId());
                }
                $repo->save($comment);
                return $change->toResponse(true);

            case ChangeAction::DELETE:
                if ($found) {
                    $repo->delete($found->getId());
                }
                return $change->toResponse(true);
        }

        return $change->toResponse(false, 'wrong action');
    }

    private function applyPoints(ChangeRequest $change): ChangeResponse
    {
        $repo = $this->repos->correctorPoints();

        $points = $repo->new();
        $data = $change->getPayload();

        $this->entity->fromPrimitives([
            'key' => $change->getKey(),
            'task_id' => $data['task_id'] ?? null,
            'writer_id' => $data['writer_id'] ?? null,
            'corrector_id' => $data['corrector_id'] ?? null,
            'criterion_id' => $data['criterion'] ?? null,
            'points' => $data['points'] ?? null,
        ], $points, CorrectorPoints::class);

        $this->entity->secure($points, CorrectorPoints::class);

        // check scope
        if ($points->getCorrectorId() !== $this->corrector?->getId()
            || !$this->repos->correctorAssignment()->hasByIds(
                $points->getWriterId(),
                $points->getCorrectorId(),
                $points->getTaskId()
            )) {
            return $change->toResponse(false, 'wrong scope');
        }

        $found = $repo->oneByTaskIdAndWriterIdAndKey($points->getTaskId(), $points->getWriterId(), $points->getKey());
        switch ($change->getAction()) {

            case ChangeAction::SAVE:
                if ($found) {
                    $points->setId($found->getId());
                }

                // check and assign comment
                $comment = null;
                if ($data['comment_key'] !== null) {
                    $comment = $this->repos->correctorComment()->oneByTaskIdAndWriterIdAndKey(
                        $points->getTaskId(),
                        $points->getWriterId(),
                        $data['comment_key'] ?? null,
                    );
                    if ($comment === null) {
                        return $change->toResponse(false, 'wrong comment');
                    }
                    $points->setCommentId($comment->getId());
                }

                // check criterion
                $criterion = null;
                if ($points->getCriterionId() !== null) {
                    $criteria = $this->getCriteriaForTaskAndCorrector($points->getTaskId(), $points->getCorrectorId());
                    $criterion = $criteria[$points->getCriterionId()] ?? null;
                    if ($criterion === null) {
                        return $change->toResponse(false, 'wrong criterion');
                    }
                    if ($criterion->getGeneral() && $points->getCommentId() !== null) {
                        return $change->toResponse(false, 'points assigned to comment and general criterion');
                    }
                    if (!$criterion->getGeneral() && $points->getCommentId() === null) {
                        return $change->toResponse(false, 'points assigned to non-general criterion without comment');
                    }
                }
                if ($points->getCommentId() === null && $points->getCriterionId() === null) {
                    return $change->toResponse(false, 'criterion or comment needed for points');
                }

                $repo->save($points);
                return $change->toResponse(true);

            case ChangeAction::DELETE:
                if ($found) {
                    $repo->delete($found->getId());
                }
                return $change->toResponse(true);
        }

        return $change->toResponse(false, 'wrong action');
    }

    private function applySummary(ChangeRequest $change): ChangeResponse
    {
        return $change->toResponse(false, 'wrong action');
    }

    private function applyPreferences(ChangeRequest $change): ChangeResponse
    {
        return $change->toResponse(false, 'wrong action');
    }

    private function applySnippets(ChangeRequest $change): ChangeResponse
    {
        return $change->toResponse(false, 'wrong action');
    }


    private function getSummaryForAssignment(CorrectorAssignment $assignment): CorrectorSummary
    {
        return  $this->repos->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId(
            $assignment->getTaskId(),
            $assignment->getWriterId(),
            $assignment->getCorrectorId()
        ) ?? $this->getNewSummaryForAssignment($assignment);
    }

    private function getNewSummaryForAssignment(CorrectorAssignment $assignment): CorrectorSummary
    {
        return $this->repos->correctorSummary()->new()
            ->setTaskId($assignment->getTaskId())
            ->setWriterId($assignment->getWriterId())
            ->setCorrectorId($assignment->getCorrectorId());
    }

    private function getCriteriaForTaskAndCorrector(int $task_id, int $corrector_id)
    {

        switch ($this->correction_settings->getCriteriaMode()) {
            case CriteriaMode::CORRECTOR:
                break;
            case CriteriaMode::FIXED:
                $corrector_id = null;
                break;
            case CriteriaMode::NONE:
                return [];
        }

        if (!isset($this->criteria[$task_id][(int) $corrector_id])) {
            $this->criteria[$task_id][(int) $corrector_id] = [];
            foreach ($this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($task_id, $corrector_id) as $criterion) {
                $this->criteria[$task_id][(int) $corrector_id][$criterion->getId()] = $criterion;
            }
        }
        return $this->criteria[$task_id][(int) $corrector_id];
    }
}
