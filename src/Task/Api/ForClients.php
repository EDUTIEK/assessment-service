<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\Manager as ManagerInterface;
use Edutiek\AssessmentService\Task\Manager\Service as ManagerService;
use Edutiek\AssessmentService\Task\Resource\FullService as ResourceFullService;
use Edutiek\AssessmentService\Task\Resource\Service as ResourceService;
use Edutiek\AssessmentService\Task\Settings\FullService as SettingsFullService;
use Edutiek\AssessmentService\Task\Settings\Service as SettingsService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as CorrectorAssignmentsFullService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\Service as CorrectorAssignmentsService;
use Edutiek\AssessmentService\Task\CorrectionSettings\FullService as CorrectionSettingsFullService;
use Edutiek\AssessmentService\Task\CorrectorSummary\FullService as CorrectorSummaryFullService;
use Edutiek\AssessmentService\Task\CorrectorSummary\Service as CorrectorSummaryService;
use Edutiek\AssessmentService\Task\RatingCriterion\Service as RatingCriterionService;
use Edutiek\AssessmentService\Task\RatingCriterion\FullService as RatingCriterionFullService;
use Edutiek\AssessmentService\Task\AssessmentStatus\Service as StatusService;
use Edutiek\AssessmentService\Task\AssessmentStatus\FullService as StatusFullService;
use Edutiek\AssessmentService\Task\Format\FullService as FormatFullService;
use Edutiek\AssessmentService\Task\Format\Service as FormatService;
use Edutiek\AssessmentService\Task\CorrectionProcess\FullService as CorrectionProcessFullService;
use Edutiek\AssessmentService\Task\CorrectionProcess\Service as CorrectionProcessService;

class ForClients
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function manager(): ManagerInterface
    {
        return $this->instances[ManagerService::class] ??= new ManagerService(
            $this->ass_id,
            $this->user_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
            $this->dependencies->typeApis(),
            $this->internal->language("de")
        );
    }

    public function resource(int $task_id): ResourceFullService
    {
        return $this->instances[ResourceService::class][$task_id] ??= new ResourceService(
            $task_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->fileStorage(),
        );
    }

    public function settings(int $task_id): SettingsFullService
    {
        return $this->instances[SettingsService::class][$task_id] ??= new SettingsService(
            $this->ass_id,
            $task_id,
            $this->dependencies->repositories()
        );
    }

    public function correctorAssignments(): CorrectorAssignmentsFullService
    {
        return $this->internal->correctorAssignments($this->ass_id, $this->user_id);
    }

    public function correctionSettings(): CorrectionSettingsFullService
    {
        return $this->internal->correctionSettings($this->ass_id, $this->user_id);
    }

    public function summary(int $task_id): CorrectorSummaryFullService
    {
        return $this->internal->correctorSummary($task_id);
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
        return $this->internal->assessmentStatus($this->ass_id, $this->user_id);
    }

    public function format(): FormatFullService
    {
        return $this->instances[FormatService::class] ??= new FormatService(
            $this->dependencies->systemApi()->language(),
            $this->dependencies->assessmentApis($this->ass_id, $this->user_id)->assessment_grading()
        );
    }

    public function correctionProcess(): CorrectionProcessFullService
    {
        return $this->instances[CorrectionProcessService::class] ??= new CorrectionProcessService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->assessmentApis($this->ass_id, $this->user_id)->writer(),
            $this->dependencies->assessmentApis($this->ass_id, $this->user_id)->correction_process(),
            $this->dependencies->assessmentApis($this->ass_id, $this->user_id)->logEntry(),
            $this->dependencies->assessmentApis($this->ass_id, $this->user_id)->correctionSettings()->get(),
            $this->dependencies->assessmentApis($this->ass_id, $this->user_id)->corrector(),
        );
    }
}
