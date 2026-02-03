<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\EssayTask\Api;

use Edutiek\AssessmentService\EssayTask\AssessmentStatus\FullService as StatusFullService;
use Edutiek\AssessmentService\EssayTask\Essay\ClientService as EssayClientService;
use Edutiek\AssessmentService\EssayTask\EssayImport\FullService as FullImportService;
use Edutiek\AssessmentService\EssayTask\WritingSettings\FullService as WritingSettingsFullService;
use Edutiek\AssessmentService\EssayTask\WritingSteps\FullService as WritingStepsFullService;
use Edutiek\AssessmentService\System\BackgroundTask\Job;

readonly class ForClients
{
    public function __construct(
        private int $ass_id,
        private int $user_id,
        private Internal $internal,
    ) {
    }

    public function essay(bool $as_admin = false): EssayClientService
    {
        return $this->internal->essay($this->ass_id, $this->user_id, $as_admin);
    }

    public function writingSettings(): WritingSettingsFullService
    {
        return $this->internal->writingSettings($this->ass_id);
    }

    public function assessmentStatus(): StatusFullService
    {
        return $this->internal->assessmentStatus($this->ass_id, $this->user_id);
    }

    public function backgroundTask(string $class): Job
    {
        return $this->internal->backgroundTask($this->ass_id, $this->user_id, $class);
    }

    public function import(int $task_id): FullImportService
    {
        return $this->internal->import($this->ass_id, $task_id, $this->user_id);
    }

    public function writingSteps(): WritingStepsFullService
    {
        return $this->internal->writingSteps($this->ass_id, $this->user_id);
    }

}
