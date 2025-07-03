<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Corrector;

Interface FullService extends ReadService
{
    public function hasReports(): bool;
}