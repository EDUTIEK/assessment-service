<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Authentication\FullService as AuthenticationFullService;
use Edutiek\AssessmentService\Assessment\Authentication\Service as AuthenticationService;
use Edutiek\AssessmentService\Assessment\Apps\RestService as RestService;
use Edutiek\AssessmentService\Assessment\Apps\Service as AppService;
use Edutiek\AssessmentService\Assessment\Writer\ReadService as WriterReadService;
use Edutiek\AssessmentService\Assessment\Writer\Service as WriterService;

class ForTasks
{
    private array $instances = [];

    public function __construct(
        private readonly int $ass_id,
        private readonly Dependencies $dependencies
    ) {
    }

    public function writer(): WriterReadService
    {
        return $this->instances[WriterReadService::class] ??= new WriterService(
            $this->ass_id,
            $this->dependencies->repositories()
        );
    }
}
