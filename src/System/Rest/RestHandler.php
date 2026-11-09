<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Rest;

interface RestHandler
{
    /**
     * Handle a REST call
     */
    public function handle(): void;
}
