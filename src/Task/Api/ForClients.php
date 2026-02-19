<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Api;

use Edutiek\AssessmentService\Assessment\TaskInterfaces\TaskManager as ManagerInterface;
use Edutiek\AssessmentService\Task\AssessmentStatus\FullService as StatusFullService;
use Edutiek\AssessmentService\Task\CorrectionProcess\FullService as CorrectionProcessFullService;
use Edutiek\AssessmentService\Task\CorrectionSettings\FullService as CorrectionSettingsFullService;
use Edutiek\AssessmentService\Task\CorrectorAssignments\FullService as CorrectorAssignmentsFullService;
use Edutiek\AssessmentService\Task\CorrectorSummary\ReadService as CorrectorSummaryFullService;
use Edutiek\AssessmentService\Task\CorrectorTemplate\FullService as CorrectorTemplateFullService;
use Edutiek\AssessmentService\Task\Format\FullService as FormatFullService;
use Edutiek\AssessmentService\Task\RatingCriterion\FullService as RatingCriterionFullService;
use Edutiek\AssessmentService\Task\Resource\FullService as ResourceFullService;
use Edutiek\AssessmentService\Task\Settings\FullService as SettingsFullService;

readonly class ForClients
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Internal $internal
    ) {
    }

    public function manager(): ManagerInterface
    {
        return $this->internal->taskManager($this->ass_id, $this->user_id);
    }

    public function resource(int $task_id): ResourceFullService
    {
        return $this->internal->resource($task_id);
    }

    public function settings(int $task_id): SettingsFullService
    {
        return $this->internal->settings($this->ass_id, $task_id);
    }

    public function correctorAssignments(): CorrectorAssignmentsFullService
    {
        return $this->internal->correctorAssignments($this->ass_id, $this->user_id);
    }

    public function correctionSettings(): CorrectionSettingsFullService
    {
        return $this->internal->correctionSettings($this->ass_id, $this->user_id);
    }

    public function correctorSummary(): CorrectorSummaryFullService
    {
        return $this->internal->correctorSummary($this->ass_id, $this->user_id);
    }

    public function correctorTemplates(): CorrectorTemplateFullService
    {
        return $this->internal->correctorTemplate($this->ass_id, $this->user_id);
    }

    public function ratingCriterion(int $task_id): RatingCriterionFullService
    {
        return $this->internal->ratingCriterion($task_id, $this->ass_id, $this->user_id);
    }

    public function assessmentStatus(): StatusFullService
    {
        return $this->internal->assessmentStatus($this->ass_id, $this->user_id);
    }

    public function format(): FormatFullService
    {
        return $this->internal->format($this->ass_id, $this->user_id);
    }

    public function correctionProcess(): CorrectionProcessFullService
    {
        return $this->internal->correctionProcess($this->ass_id, $this->user_id);
    }
}
