<?php

declare(strict_types = 1);

namespace Edutiek\AssessmentService\EssayTask\CorrectionSettings;

use Edutiek\AssessmentService\EssayTask\Api\ApiException;
use Edutiek\AssessmentService\EssayTask\AssessmentStatus\FullService as AssessmentStatus;
use Edutiek\AssessmentService\EssayTask\Data\CorrectionSettings;
use Edutiek\AssessmentService\EssayTask\Data\CriteriaMode;
use Edutiek\AssessmentService\EssayTask\Data\Repositories;
use Edutiek\AssessmentService\EssayTask\Data\TaskSettings;
use Edutiek\AssessmentService\Task\CorrectorAssignments\ReadService as CorrectorAssignmentService;

class Service implements FullService
{
    private ?array $task_ids = null;

    public function __construct(
        private int $ass_id,
        private Repositories $repos,
        private CorrectorAssignmentService $corrector_assignment_service,
        private AssessmentStatus $assessment_status,
    ) {
    }

    public function get() : CorrectionSettings
    {
        return $this->repos->correctionSettings()->one($this->ass_id) ??
            $this->repos->correctionSettings()->new()->setAssId($this->ass_id);
    }

    public function save(CorrectionSettings $settings) : void
    {
        $this->checkScope($settings);

        $existing = $this->get();
        if ($existing->getCriteriaMode() !== $settings->getCriteriaMode() && $this->assessment_status->hasAuthorizedSummaries()) {
            throw new ApiException("changing criteria mode not allowed if corrections are authorized", ApiException::CORRECTION_STATUS);
        }

        $this->repos->correctionSettings()->save($settings);
        $this->handleCriteriaModeChange($existing->getCriteriaMode(), $settings->getCriteriaMode());
    }

    /**
     * @return int[]
     */
    private function allTaskIds() : array
    {
        return $this->task_ids ??= array_map(fn (TaskSettings $x) => $x->getTaskId(), $this->repos->taskSettings()->allByAssId($this->ass_id));
    }

    /**
     * @return array<int, int[]>
     */
    private function allCorrectorIdsByTask()
    {
        $correctors_by_task = [];
        foreach ($this->corrector_assignment_service->all() as $assignment) {
            $correctors_by_task[$assignment->getTaskId()][] = $assignment->getCorrectorId();
        }
        return array_map(fn (array $x) => array_unique($x), $correctors_by_task);
    }

    /**
     * Handle a change of the criteria mode
     */
    private function handleCriteriaModeChange(CriteriaMode $old_mode, CriteriaMode $new_mode)
    {
        switch (CriteriaModeTransition::fromTransition($old_mode, $new_mode)) {
            case CriteriaModeTransition::NoneToFixed:
            case CriteriaModeTransition::NoneToCorrector:
                foreach ($this->allTaskIds() as $task_id) {
                    $this->purgeAllPoints($task_id);
                }
                break;
            case CriteriaModeTransition::FixedToNone:
            case CriteriaModeTransition::CorrectorToNone:
                foreach ($this->allCorrectorIdsByTask() as $task_id => $corrector_ids) {
                    $this->purgeCriteriaInPoints($task_id, $corrector_ids);
                    $this->deleteAllCriteria($task_id);
                }
                break;
            case CriteriaModeTransition::FixedToCorrector:
                foreach ($this->allCorrectorIdsByTask() as $task_id => $corrector_ids) {
                    $this->copyFixedCriteriaWithPoints($task_id, $corrector_ids);
                }
                break;
            case CriteriaModeTransition::CorrectorToFixed:
                foreach ($this->allCorrectorIdsByTask() as $task_id => $corrector_ids) {
                    $this->purgeAllPoints($task_id);
                    $this->deletePersonalCriteria($task_id, $corrector_ids);
                }
                break;
        }
    }

    /**
     * Purge all points given by correctors in the task
     */
    private function purgeAllPoints(int $task_id)
    {
        foreach ($this->repos->essay()->allByTaskId($task_id) as $essay) {
            $this->repos->correctorPoints()->deleteByEssayId($essay->getId());
        }
    }

    /**
     * Copy general criteria to the correctors and re-assign the points
     */
    private function copyFixedCriteriaWithPoints(int $task_id, array $corrector_ids)
    {
        $fixed_criteria = [];
        foreach ($this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($task_id, null) as $criterion) {
            if ($criterion->getCorrectorId() === null) {
                $fixed_criteria[$criterion->getId()] = $criterion;
            }
        }


        foreach ($corrector_ids as $corrector_id) {
            $matching = [];
            foreach ($fixed_criteria as $criterion) {
                $corr_criterion = clone($criterion);
                $corr_criterion->setId(0);
                $corr_criterion->setCorrectorId($corrector_id);
                $this->repos->ratingCriterion()->save($corr_criterion);
                $matching[$criterion->getId()] = $corr_criterion->getId();
            }

            foreach ($this->repos->essay()->allByTaskId($task_id) as $essay) {
                foreach ($this->repos->correctorPoints()->allByEssayIdAndCorrectorId(
                    $essay->getId(),
                    $corrector_id
                ) as $points) {
                    if (isset($matching[$points->getCriterionId()])) {
                        $points->setCriterionId($matching[$points->getCriterionId()]);
                        $this->repos->correctorPoints()->save($points);
                    }
                }
            }
        }
    }

    /**
     * Sum up criteria points that are assigned to comments
     * Delete the points that are only assigned to criteria
     */
    private function purgeCriteriaInPoints(int $task_id, array $corrector_ids)
    {
        foreach ($corrector_ids as $corrector_id) {
            foreach ($this->repos->essay()->allByTaskId($task_id) as $essay) {
                $comment_points = [];
                foreach ($this->repos->correctorPoints()->allByEssayIdAndCorrectorId($essay->getId(), $corrector_id) as $points) {
                    if ($points->getCommentId() !== null) {
                        $comment_points[$points->getCommentId()] = ($comment_points[$points->getCommentId()] ?? 0) + $points->getPoints();
                    }
                }
                $this->repos->correctorPoints()->deleteByEssayIdAndCorrectorId($essay->getId(), $corrector_id);

                foreach ($comment_points as $comment_id => $sum_of_points) {
                    $points = $this->repos->correctorPoints()->new()
                                          ->setEssayId($essay->getId())
                                          ->setCorrectorId($corrector_id)
                                          ->setCommentId($comment_id)
                                          ->setPoints($sum_of_points);

                    $this->repos->correctorPoints()->save($points);
                }
            }
        }
    }

    /**
     * Delete all general and personal rating criteria
     */
    private function deleteAllCriteria(int $task_id)
    {
        $this->repos->ratingCriterion()->deleteByTaskId($task_id);
    }

    /**
     * Delete the individual rating criteria of correctors
     */
    private function deletePersonalCriteria(int $task_id, array $corrector_ids)
    {
        foreach ($corrector_ids as $corrector_id) {
            $this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($task_id, $corrector_id);
        }
    }

    private function checkScope(CorrectionSettings $settings)
    {
        if ($settings->getAssId() !== $this->ass_id) {
            throw new ApiException("wrong ass_id", ApiException::ID_SCOPE);
        }
    }
}
