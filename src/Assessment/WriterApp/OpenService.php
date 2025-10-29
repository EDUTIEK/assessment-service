<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterApp;

interface OpenService
{
    /**
     * Open the frontend for writing
     */
    public function open(string $return_url): void;
}
