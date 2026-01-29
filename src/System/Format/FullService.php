<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Format;

use DateTimeInterface;

interface FullService
{
    /**
     * Format a date for writing log entries
     */
    public function logDate(?DateTimeInterface $date): string;

    /**
     * Format a date for display in the user interface
     */
    public function date(?DateTimeInterface $date): string;

    /**
     * Format a date range for display in the user interface
     */
    public function dateRange(?DateTimeInterface $start, ?DateTimeInterface $end): string;

    /**
     * Human-readable file size
     */
    public function fileSize(int $size = 0, string $unit = ""): string;
}
