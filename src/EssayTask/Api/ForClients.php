<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\Essay\FullService as EssayFullService;
use Edutiek\AssessmentService\EssayTask\Essay\Service as EssayService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\Service as WritingSettingsService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\FullService as WritingSettingsFullService;
use Edutiek\AssessmentService\EssayTask\CorrectionSettings\Service as CorrectionSettingsService;
use Edutiek\AssessmentService\EssayTask\CorrectionSettings\FullService as CorrectionSettingsFullService;
use Edutiek\AssessmentService\EssayTask\RatingCriterion\Service as RatingCriterionService;
use Edutiek\AssessmentService\EssayTask\RatingCriterion\FullService as RatingCriterionFullService;
use Edutiek\AssessmentService\EssayTask\AssessmentStatus\Service as StatusService;
use Edutiek\AssessmentService\EssayTask\AssessmentStatus\FullService as StatusFullService;
use Edutiek\AssessmentService\EssayTask\TaskSettings\Service as TaskSettingsService;
use Edutiek\AssessmentService\EssayTask\TaskSettings\FullService as TaskSettingsFullService;
use Edutiek\AssessmentService\EssayTask\CorrectorSummary\FullService;
use Edutiek\AssessmentService\EssayTask\CorrectorSummary\Service;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private int $user_id,
        private readonly Dependencies $dependencies
    ) {
    }

    public function essay(): EssayFullService
    {
        return $this->instances[EssayFullService::class] = new EssayService(
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($this->ass_id, $this->user_id)->writer()
        );
    }

    public function correctionSettings(): CorrectionSettingsFullService
    {
        return $this->instances[CorrectionSettingsService::class] = new CorrectionSettingsService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->taskApi($this->ass_id, $this->user_id)->correctorAssignments(),
            $this->assessmentStatus()
        );
    }

    public function writingSettings(): WritingSettingsFullService
    {
        return $this->instances[WritingSettingsService::class] = new WritingSettingsService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }

    public function ratingCriterion(int $task_id): RatingCriterionFullService
    {
        return $this->instances[RatingCriterionService::class][$task_id] ??= new RatingCriterionService(
            $task_id,
            $this->dependencies->repositories()
        );
    }

    public function assessmentStatus(): StatusFullService
    {
        return $this->instances[StatusService::class] = new StatusService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApi($this->ass_id, $this->user_id)->writer()
        );
    }

    public function taskSettings(int $task_id): TaskSettingsFullService
    {
        return $this->instances[TaskSettingsService::class][$task_id] ??= new TaskSettingsService(
            $this->ass_id,
            $task_id,
            $this->dependencies->repositories()
        );
    }

    public function summary(int $task_id): FullService
    {
        return $this->instances[Service::class] ??= new Service($task_id, $this->dependencies->repositories());
    }
}
