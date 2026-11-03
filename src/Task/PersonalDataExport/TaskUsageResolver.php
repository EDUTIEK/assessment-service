<?php

namespace Edutiek\AssessmentService\Task\PersonalDataExport;

use Edutiek\AssessmentService\Assessment\PersonalDataExport\AbstractUsageResolver;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\UsageScope;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\UsageQuery;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\UsageNode;
use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\Data\Settings;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Group;
use Edutiek\AssessmentService\Assessment\PersonalDataExport\Attributes\Text;

class TaskUsageResolver extends AbstractUsageResolver
{
    /**
     * @var array<string, bool> Track which ass_id+corrector_id combinations have been processed for CorrectorSnippets
     */
    private array $processed_corrector_snippets = [];

    /**
     * @var array<string, bool> Track which writer_ids have been processed for CorrectorPrefs
     */
    private array $processed_corrector_prefs = [];

    public function __construct(
        private Repositories $repositories
    ) {
    }

    public function componentId(): string
    {
        return "Task";
    }

    /**
     * @inheritDoc
     */
    public function resolveUsages(UsageQuery $query): array
    {
        return array_map(
            fn (int $task_id) => $this->createScope($query, null, $task_id),
            $this->repositories->settings()->idsByAssId($query->getAssId())
        );
    }

    /**
     * @inheritDoc
     */
    public function collectData(UsageScope $scope, array $sub_nodes): UsageNode
    {
        $task_id = $scope->getTaskId();
        $ass_id = $scope->getAssId();
        $groups_for_assessment = [];
        $task_contains_data = false;

        $assembly = new TaskGroupAssembly($scope);

        if ($scope->isCorrector()) {
            // Add CorrectorTaskPrefs for this task
            $corrector_task_prefs = $this->repositories->correctorTaskPrefs()->oneByCorrectorIdAndTaskId($scope->getCorrectorId(), $task_id);
            if ($corrector_task_prefs !== null) {
                $assembly->addCorrectorTaskPrefs($corrector_task_prefs);
                $task_contains_data = true;
            }

            // Add CorrectorTemplates for this task
            $corrector_template = $this->repositories->correctorTemplates()->oneByTaskIdAndCorrectorId($scope->getCorrectorId(), $task_id);
            if ($corrector_template !== null) {
                $assembly->addCorrectorTemplates($corrector_template);
                $task_contains_data = true;
            }

            $rating_criterion = $this->repositories->ratingCriterion()->allByTaskIdAndCorrectorId($task_id, $scope->getCorrectorId());
            if(!empty($rating_criterion)) {
                $assembly->addOwnRatingCriterions($rating_criterion);
                $task_contains_data = true;
            }
        }

        if ($scope->isWriter()) {
            // Add WriterAnnotations for this task
            $annotations = $this->repositories->writerAnnotation()->allByWriterIdAndTaskId($scope->getWriterId(), $task_id);
            if(!empty($annotations)) {
                $assembly->addWriterAnnotations($annotations);
                $task_contains_data = true;
            }
        }

        // CorrectorAssignment
        $corrector_assignments = [];
        if ($scope->isWriter()) {
            $corrector_assignments = array_merge($corrector_assignments, $this->repositories->correctorAssignment()->allByTaskIdAndWriterId($task_id, $scope->getWriterId()));
        }

        if ($scope->isCorrector()) {
            $corrector_assignments = array_merge($corrector_assignments, $this->repositories->correctorAssignment()->allByTaskIdAndCorrectorId($task_id, $scope->getCorrectorId()));
        }
        $rating_criterion = $this->repositories->ratingCriterion()->allByTaskId($scope->getTaskId());
        $correction_assembly = new CorrectionGroupAssembly($scope);
        $correction_assembly->setRatingCriterion($rating_criterion);

        foreach ($corrector_assignments as $assignment) {
            $summary = $this->repositories->correctorSummary()->oneByTaskIdAndWriterIdAndCorrectorId($task_id, $assignment->getWriterId(), $assignment->getCorrectorId());
            $comments = $this->repositories->correctorComment()->allByTaskIdAndWriterIdAndCorrectorId($task_id, $assignment->getWriterId(), $assignment->getCorrectorId());
            $points = $this->repositories->correctorPoints()->allByTaskIdAndWriterIdAndCorrectorId($task_id, $assignment->getWriterId(), $assignment->getCorrectorId());

            $correction_assembly->addCorrection($assignment, $summary, $comments, $points);
        }
        // Add the correction group to the task assembly
        if(!empty($corrector_assignments)) {
            $assembly->addSubGroup($correction_assembly->getGroup());
            $task_contains_data = true;
        }

        // Add CorrectorPrefs and CorrectorSnippets only once per assessment+writer_id or assessment+corrector_id
        // to avoid duplicates from different scopes
        $corrector_id = $scope->getCorrectorId();

        // Add CorrectorPrefs if not already processed for this corrector
        if ($corrector_id !== null && !isset($this->processed_corrector_prefs[$ass_id . '_' . $corrector_id])) {
            $corrector_prefs = $this->repositories->correctorPrefs()->one($corrector_id);
            if ($corrector_prefs !== null) {
                $corrector_prefs_assembly = new CorrectorPrefsGroupAssembly($scope);
                $corrector_prefs_assembly->addCorrectorPrefs($corrector_prefs);
                $this->processed_corrector_prefs[$ass_id . '_' . $corrector_id] = true;
                $groups_for_assessment[] = $corrector_prefs_assembly->getGroup();
            }
        }

        // Add CorrectorSnippets if not already processed for this corrector
        if ($corrector_id !== null && !isset($this->processed_corrector_snippets[$ass_id . '_' . $corrector_id])) {
            $corrector_snippets = $this->repositories->correctorSnippets()->allByCorrectorId($ass_id, $corrector_id);
            if (!empty($corrector_snippets)) {
                $corrector_snippets_assembly = new CorrectorSnippetGroupAssembly($scope);
                $corrector_snippets_assembly->addCorrectorSnippets($corrector_snippets);
                $this->processed_corrector_snippets[$ass_id . '_' . $corrector_id] = true;
                $groups_for_assessment[] = $corrector_snippets_assembly->getGroup();
            }
        }

        $final_groups = [];
        // Collect groups from sub_nodes (from other resolvers)

        foreach ($sub_nodes as $node) {
            foreach ($node->getGroupsForAssessment() as $group) {
                $groups_for_assessment[] = $group;
            }
            foreach ($node->getGroupsForTask() as $group) {
                $assembly->addSubGroup($group);
                $task_contains_data = true;
            }
            foreach ($node->getGroupsFinal() as $group) {
                $final_groups[] = $group;
            }
        }

        if($task_contains_data) {
            // Add task settings
            $settings = $this->repositories->settings()->one($task_id);
            if ($settings !== null) {
                $assembly->addTaskSettings($settings);
            }
            $groups_for_assessment[] = $assembly->getGroup();
        }
        // Add task settings
        $settings = $this->repositories->settings()->one($task_id);
        if ($settings !== null) {
            $assembly->addTaskSettings($settings);
        }

        return $this->createNode($scope, $final_groups, $groups_for_assessment, []);
    }
}
