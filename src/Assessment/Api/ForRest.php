<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Api;

use Edutiek\AssessmentService\System\Rest\RestHandler;

readonly class ForRest
{
    public function __construct(
        private Internal $internal
    ) {
    }

    /**
     * Common handler for all REST calls
     */
    public function service(): RestHandler
    {
        return $this->internal->appService();
    }
}
