<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\CorrectorApp;

interface OpenService
{
    public function open(OpenMode $mode): void;
}
