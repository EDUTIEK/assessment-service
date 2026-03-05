<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\RatingCriterion;

use Edutiek\AssessmentService\Task\Data\Repositories;
use Edutiek\AssessmentService\Task\RatingCriterion\FullService;
use Edutiek\AssessmentService\Task\Data\RatingCriterion;
use Edutiek\AssessmentService\Task\Api\ApiException;
use Edutiek\AssessmentService\Task\Data\CriteriaMode;
use Edutiek\AssessmentService\Task\CorrectionSettings\ReadService as SettingsService;
use ILIAS\Rating;
use Edutiek\AssessmentService\Task\Api\Dependencies;

readonly class Service implements FullService
{
    public function __construct(
        private int $task_id,
        private Repositories $repos,
        private SettingsService $settings_service
    ) {
    }

    public function allByCorrectorId(?int $corrector_id): array
    {
        return $this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($this->task_id, $corrector_id);
    }

    public function allForCorrector(int $corrector_id): array
    {
        switch ($this->settings_service->get()->getCriteriaMode()) {
            case CriteriaMode::CORRECTOR:
                break;
            case CriteriaMode::FIXED:
                $corrector_id = null;
                break;
            case CriteriaMode::NONE:
                return [];
        }

        $criteria = $this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($this->task_id, $corrector_id);
        usort($criteria, fn(RatingCriterion $c1, RatingCriterion $c2) => $c1->getTitle() <=> $c2->getTitle());

        return $criteria;
    }


    public function new(): RatingCriterion
    {
        return $this->repos->ratingCriterion()->new()->setTaskId($this->task_id);
    }

    public function save(RatingCriterion $criterion): void
    {
        $this->checkScope($criterion);
        $this->repos->ratingCriterion()->save($criterion);
    }

    private function checkScope(RatingCriterion $criterion)
    {
        if ($criterion->getTaskId() !== $this->task_id) {
            throw new ApiException("wrong task_id", ApiException::ID_SCOPE);
        }
    }

    public function one(int $criterion_id): ?RatingCriterion
    {
        return $this->repos->ratingCriterion()->one($criterion_id);
    }

    public function delete(RatingCriterion $criterion)
    {
        $this->checkScope($criterion);
        $this->repos->ratingCriterion()->delete($criterion->getId());
    }

    public function copyFromTask(int $to_task_id, int $from_task_id)
    {
        $origin_criteria = $this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($from_task_id, null);
        $this->repos->ratingCriterion()->deleteByTaskId($to_task_id);

        foreach ($origin_criteria as $criterion) {
            $new = clone $criterion;
            $new->setId(0);
            $new->setCorrectorId(null);
            $new->setTaskId($to_task_id);
            $this->repos->ratingCriterion()->save($new);
        }
    }

    public function copyFromCorrector(int $task_id, int $to_corrector_id, ?int $from_corrector_id)
    {
        if( $from_corrector_id !== null) {
            $prefs = $this->repos->correctorTaskPrefs()->oneByCorrectorIdAndTaskId($from_corrector_id, $task_id);

            if(!($prefs?->getCriterionCopy()??false)) {
                throw new ApiException("criterion copy is not enabled", ApiException::CRITERION_COPY_DISABLED);
            }
        }
        $origin_criteria = $this->repos->ratingCriterion()->allByTaskIdAndCorrectorId($task_id, $from_corrector_id);
        $this->repos->ratingCriterion()->deleteByCorrectorId($to_corrector_id);
        $has_comment_criterion = false;

        foreach ($origin_criteria as $criterion) {
            $new = clone $criterion;
            if (!$new->getGeneral()) {
                $has_comment_criterion = true;
            }
            $new->setId(0);
            $new->setCorrectorId($to_corrector_id);
            $new->setTaskId($task_id);
            $this->repos->ratingCriterion()->save($new);
        }

        if ($has_comment_criterion) {
            $this->repos->correctorPoints()->deleteWithoutCriteria($task_id, $to_corrector_id);
        }
    }
}
