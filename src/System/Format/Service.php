<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Format;

use DateTimeInterface;
use DateTimeZone;

readonly class Service implements FullService
{
    public function __construct(
        private string $language,
        private DateTimeZone $timezone
    ) {}

    public function dates(?DateTimeInterface $start = null, ?DateTimeInterface $end = null) : string
    {
        $parts = [];
        foreach ([$start, $end] as $date) {
            if (!empty($date)) {
                $date = (clone $date)->setTimezone($this->timezone);

                if ($this->language == 'de') {
                    $parts[] = $date->format('d.m.Y H:i');
                }
                else {
                    $parts[] = $date->format('Y-m-d H:i');
                }
            }
        }

        return implode(' - ', array_unique($parts));
    }

    public function fileSize(int $size = 0, string $unit = ""): string
    {
        if ((!$unit && $size >= 1 << 30) || $unit == "GB") {
            return number_format($size / (1 << 30), 2) . "GB";
        }
        if ((!$unit && $size >= 1 << 20) || $unit == "MB") {
            return number_format($size / (1 << 20), 2) . "MB";
        }
        if ((!$unit && $size >= 1 << 10) || $unit == "KB") {
            return number_format($size / (1 << 10), 2) . "KB";
        }
        return number_format($size) . " bytes";
    }
}