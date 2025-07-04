<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\System\Format;

use DateTimeImmutable;
use DateTimeZone;
use Closure;
use Edutiek\AssessmentService\System\Language\FullService as Language;

readonly class Service implements FullService
{
    /**
     * @param Closure(DateTimeInterface): string $format_date
     */
    public function __construct(
        private Closure $format_date,
        private DateTimeZone $timezone,
        private Language $language
    ) {}

    public function dateRange(?DateTimeImmutable $start, ?DateTimeImmutable $end): string
    {
        $txt = $this->language->txt(...);

        $text = match([!!$start, !!$end]) {
            [true, false] => $txt('period_only_from') . ' ' . ($this->format_date)($start),
            [false, true] => $txt('period_only_until') . ' ' . ($this->format_date)($end),
            [true, true] => join(' ', [$txt('period_from'), ($this->format_date)($start), $txt('period_until'), ($this->format_date)($end)]),
            [false, false] => $txt('not_specified'),
        };

        return $text;
    }

    public function fileSize(int $size = 0, string $unit = ''): string
    {
        if ((!$unit && $size >= 1 << 30) || $unit == $this->language->txt('gb')) {
            return number_format($size / (1 << 30), 2) . $this->language->txt('gb');
        }
        if ((!$unit && $size >= 1 << 20) || $unit == $this->language->txt('mb')) {
            return number_format($size / (1 << 20), 2) . $this->language->txt('mb');
        }
        if ((!$unit && $size >= 1 << 10) || $unit == $this->language->txt('kb')) {
            return number_format($size / (1 << 10), 2) . $this->language->txt('kb');
        }
        return number_format($size) . $this->language->txt('bytes');
    }

    /**
     * $duration in seconds.
     */
    public function duration(int $duration): string
    {
        $times = [];
        $times['day'] = floor($duration / (24 * 3600));
        $times['hour'] = floor(($duration - $times['day'] * 24 * 3600) / 3600);
        $times['minute'] = floor(($duration - $times['day'] * 24 * 3600 - $times['hour'] * 3600) / 60);
        $times['second'] = $duration % 60;
        $txt = $this->language->txt(...);

        $parts = [];
        foreach ($times as $name => $value) {
            $parts[] = sprintf($txt($value === 1 ? ('one_' . $name) : ('x_' . $name . 's')), $value);
        }

        return implode(' ', $parts);
    }
}
