<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WorkingTime;

use DateTimeZone;
use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\Data\OrgaSettings;
use Edutiek\AssessmentService\Assessment\Data\ValidationError;
use Edutiek\AssessmentService\Assessment\Data\ValidationErrorStore;
use Edutiek\AssessmentService\System\Language\FullService as Language;
use Edutiek\AssessmentService\Assessment\Data\Writer;
use Edutiek\AssessmentService\System\Format\FullService as SystemFormat;
use Edutiek\AssessmentService\Assessment\WorkingTime\Service as WorkingTimeService;

/**
 * Calculation of the common or individual working time
 */
readonly class Service implements FullService
{
    private DateTimeZone $time_zone;
    private ?DateTimeImmutable $common_earliest_start;
    private ?DateTimeImmutable $common_latest_end;
    private ?DateTimeImmutable $writer_earliest_start;
    private ?DateTimeImmutable $writer_latest_end;
    private ?int $common_time_limit_minutes;
    private ?int $writer_time_limit_minutes;
    private ?DateTimeImmutable $working_start;
    private bool $solution_available;
    private ?DateTimeImmutable $solution_available_date;

    public function __construct(
        private Language $language,
        OrgaSettings $settings,
        Writer|IndividualWorkingTime|null $writer = null
    ) {
        $this->time_zone = new DateTimeZone(date_default_timezone_get());

        $this->common_earliest_start = $settings->getWritingStart();
        $this->common_latest_end = $settings->getWritingEnd();
        $this->common_time_limit_minutes = $settings->getWritingLimitMinutes();
        $this->solution_available = $settings->getSolutionAvailable();
        $this->solution_available_date = $settings->getSolutionAvailableDate();

        if ($writer !== null) {
            $this->writer_earliest_start = $writer->getEarliestStart();
            $this->writer_latest_end = $writer->getLatestEnd();
            $this->working_start = $writer->getWorkingStart();
            $this->writer_time_limit_minutes = $writer->getTimeLimitMinutes();
        } else {
            $this->writer_earliest_start = null;
            $this->writer_latest_end = null;
            $this->working_start = null;
            $this->writer_time_limit_minutes = null;
        }
    }

    /**
     * Get the earliest time the writing can be started
     */
    public function getEarliestStart(): ?DateTimeImmutable
    {
        return $this->writer_earliest_start ?? $this->common_earliest_start;
    }

    /**
     * Get the latest time the writing can be ended
     */
    public function getLatestEnd(): ?DateTimeImmutable
    {
        return $this->writer_latest_end ?? $this->common_latest_end;
    }

    /**
     * Get a time limit in seconds from the working start
     */
    public function getTimeLimitMinutes(): ?int
    {
        return $this->writer_time_limit_minutes ?? $this->common_time_limit_minutes;
    }

    /**
     * Get the time limit parts (days, hours, minutes)
     * @return array{?int, ?int, ?int}
     */
    public function getTimeLimitParts(): array
    {
        if ($this->getTimeLimitMinutes() === null) {
            return [null, null, null];
        }

        $limit = $this->getTimeLimitMinutes();
        $days = floor($limit / (24 * 60));
        $hours = floor(($limit - $days * 24 * 60) / 60);
        $minutes = $limit % 60;
        return [$days > 0 ? $days : null, $hours > 0 ? $hours : null, $minutes > 0 ? $minutes : null];
    }

    /**
     * Get the start of working
     * This is actively set by the writer and makes the instructions and material available
     */
    public function getWorkingStart(): ?DateTimeImmutable
    {
        return $this->working_start;
    }

    public function isSolutionAvailable(): bool
    {
        return $this->solution_available
            && ($this->solution_available_date === null || $this->solution_available_date->getTimestamp() <= time());
    }

    public function getSolutionavailableDate(): ?DateTimeImmutable
    {
        return $this->solution_available_date;
    }

    /**
     * Get the individual deadline for the end of working
     * This is calculated from the working start, time limit and latest end
     * After the deadline no inputs should be accepted
     */
    public function getWorkingDeadline(): ?DateTimeImmutable
    {
        if ($this->getWorkingStart() == null || $this->getTimeLimitMinutes() == null) {
            return $this->getLatestEnd();
        }

        $relative_end = $this->getWorkingStart()->getTimestamp() + $this->getTimeLimitMinutes() * 60;

        if ($this->getLatestEnd() == null) {
            return DateTimeImmutable::createFromFormat('U', (string) $relative_end, $this->time_zone);
        }

        $latest_end = $this->getLatestEnd()->getTimestamp();

        return DateTimeImmutable::createFromFormat('U', (string) min($relative_end, $latest_end), $this->time_zone);
    }

    /**
     * Check if the writer has individual time settings
     */
    public function isIndividual(): bool
    {
        return $this->writer_earliest_start !== null ||
            $this->writer_latest_end !== null ||
            $this->writer_time_limit_minutes !== null;
    }

    /**
     * Check if the working time has any kind of limit
     */
    public function isLimited(): bool
    {
        return $this->getEarliestStart() !== null ||
            $this->getLatestEnd() !== null ||
            $this->getTimeLimitMinutes() !== null;
    }

    /**
     * Check if the writer has a time limit counting from the start
     */
    public function hasTimeLimitFromStart(): bool
    {
        return $this->getTimeLimitMinutes() !== null;
    }

    /**
     * Check if the working has already been started
     */
    public function isStarted(): bool
    {
        return $this->getWorkingStart() !== null;
    }

    /**
     * Check if current time is before the earliest start
     * Returns false if no earliest start is defined
     */
    public function isNowBeforeAllowedTime(): bool
    {
        $start = $this->getEarliestStart();
        if ($start === null) {
            return false;
        }

        return time() < $start->getTimestamp();
    }

    /**
     * Check if the current time is after the deadline
     * Returns false if no deadline is defined
     */
    public function isNowAfterAllowedTime(): bool
    {
        $deadline = $this->getWorkingDeadline();
        if ($deadline === null) {
            return false;
        }
        return time() > $deadline->getTimestamp();
    }

    /**
     * Check if current time is within the allowed time frame
     */
    public function isNowInAllowedTime(): bool
    {
        return !$this->isNowBeforeAllowedTime() && !$this->isNowAfterAllowedTime();
    }

    /**
     * Check if the latest end is before the earliest start
     * This should cause an error
     */
    public function isEndBeforeStart(): bool
    {
        return $this->getEarliestStart() !== null &&
            $this->getLatestEnd() !== null &&
            $this->getLatestEnd()->getTimestamp() < $this->getEarliestStart()->getTimestamp();
    }

    /**
     * Check if the time limit exceeds the time span between the earliest start and latest end
     */
    public function isTimeLimitTooLong(): bool
    {
        return $this->getEarliestStart() !== null &&
            $this->getLatestEnd() != null &&
            $this->getTimeLimitMinutes() != null &&
            $this->getTimeLimitMinutes() * 60 > $this->getLatestEnd()->getTimestamp() - $this->getEarliestStart()->getTimestamp();
    }

    public function format(SystemFormat $system_format): string
    {
        if ($this->isLimited()) {
            $string = $system_format->dateRange($this->getEarliestStart(), $this->getLatestEnd());
            if ($this->getTimeLimitMinutes()) {
                $string .= ' | ' . $system_format->duration($this->getTimeLimitMinutes() * 60);
            }
            return $string;
        }

        return $this->language->txt('not_specified');
    }

    public function validate(?ValidationErrorStore $store = null): bool
    {
        $valid = true;
        if ($this->isEndBeforeStart()) {
            $store?->addValidationError(ValidationError::LATEST_END_BEFORE_EARLIEST_START);
            $valid = false;
        }
        if ($this->isTimeLimitTooLong()) {
            $store?->addValidationError(ValidationError::TIME_LIMIT_TOO_LONG);
            $valid = false;
        }
        if ($this->solution_available
            && $this->solution_available_date !== null && $this->getWorkingDeadline() !== null
            && $this->solution_available_date <= $this->getWorkingDeadline()) {
            $store?->addValidationError(ValidationError::TIME_EXCEEDS_SOLUTION_AVAILABILITY);
            $valid = false;
        }
        return $valid;
    }
}
