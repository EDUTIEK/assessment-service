<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Apps;

interface RestService
{
    /**
     * Handle a REST call
     */
    public function handle(): void;
}
