<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WriterApp;

interface OpenService
{
    public function open(string $return_url): void;
}
