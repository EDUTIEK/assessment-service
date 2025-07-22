<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Authentication\FullService as AuthenticationFullService;
use Edutiek\AssessmentService\Assessment\Authentication\Service as AuthenticationService;
use Edutiek\AssessmentService\Assessment\Apps\RestService as RestService;
use Edutiek\AssessmentService\Assessment\Apps\Service as AppService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Assessment\Writer\Service as WriterService;
use Edutiek\AssessmentService\Assessment\LogEntry\TasksService as LogEntryTasksService;
use Edutiek\AssessmentService\Assessment\LogEntry\Service as LogEntryService;
class ForTasks
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly int $user_id,
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    public function writer(): WriterReadService
    {
        return $this->instances[WriterReadService::class] ??= new WriterService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->internal->workingTimeFactory($this->user_id),
            $this->logEntry()
        );
    }

    public function logEntry(): LogEntryTasksService
    {
        return $this->instances[LogEntryService::class] ??= new LogEntryService(
            $this->ass_id,
            $this->dependencies->repositories(),
            $this->dependencies->systemApi()->language(),
            $this->dependencies->systemApi()->user()
        );
    }
}
