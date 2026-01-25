<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Format;

use DateTimeImmutable;

interface FullService
{
    public function date(?DateTimeImmutable $date): string;

    public function dateRange(?DateTimeImmutable $start, ?DateTimeImmutable $end): string;

    /**
     * Human readable file size
     */
    public function fileSize(int $size = 0, string $unit = ""): string;
}
