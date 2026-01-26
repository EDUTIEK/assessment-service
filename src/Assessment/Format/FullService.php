<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Format;

use Edutiek\AssessmentService\Assessment\Data\Writer;

interface FullService
{
    public function resultAvailability(): string;
    public function finalResult(?Writer $writer): string;
    public function writingStatus(Writer $writer): ?string;
}
