<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Format;

use DateTimeInterface;

interface FullService
{
    /**
     * Human readable dates
     */
    function dates(?DateTimeInterface $start = null, ?DateTimeInterface $end = null) : string;

    /**
     * Human readable file size
     */
    function fileSize(int $size = 0, string $unit = ""): string;
}