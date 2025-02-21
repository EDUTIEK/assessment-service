<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Authentication\FullService as AuthenticationFullService;
use Edutiek\AssessmentService\Assessment\Authentication\Service as AuthenticationService;
use Edutiek\AssessmentService\Assessment\Apps\RestService as RestService;
use Edutiek\AssessmentService\Assessment\Apps\Service as AppService;

class ForRest
{
    private array $instances = [];

    public function __construct(
        private readonly Dependencies $dependencies,
        private readonly Internal $internal
    ) {
    }

    /**
     * Common handler for all REST calls
     */
    public function service(): RestService
    {
        return $this->instances[AppService::class] ??= new AppService(
            $this->dependencies->restContext(),
            $this->internal
        );
    }
}
