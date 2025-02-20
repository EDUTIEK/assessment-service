<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Authentication\FullService as AuthenticationFullService;
use Edutiek\AssessmentService\Assessment\Authentication\Service as AuthenticationService;
use Edutiek\AssessmentService\Assessment\RestHandler\FullService as RestFullService;
use Edutiek\AssessmentService\Assessment\RestHandler\Service as RestService;

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
    public function service(): RestFullService
    {
        return $this->instances[RestService::class] ??= new RestService(
            $this->dependencies->restContext(),
            $this->internal
        );
    }
}
