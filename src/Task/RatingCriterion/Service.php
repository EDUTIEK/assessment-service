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
}
