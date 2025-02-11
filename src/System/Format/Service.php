<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Format;

use DateTimeInterface;
use DateTimeZone;

class Service implements FullService
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
}