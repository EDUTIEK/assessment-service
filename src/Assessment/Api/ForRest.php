<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\Assessment\Apps\RestService as RestService;

readonly class ForRest
{
    public function __construct(
        private Internal $internal
    ) {
    }

    /**
     * Common handler for all REST calls
     */
    public function service(): RestService
    {
        return $this->internal->appService();
    }
}
