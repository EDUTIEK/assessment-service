<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\WorkingTime;

use DateTimeImmutable;
use Edutiek\AssessmentService\System\Format\FullService as SystemFormat;

interface FullService
{
    public function getEarliestStart(): ?DateTimeImmutable;
    public function getLatestEnd(): ?DateTimeImmutable;
    public function getTimeLimitMinutes(): ?int;
    public function getTimeLimitParts(): array;
    public function getWorkingStart(): ?DateTimeImmutable;
    public function getWorkingDeadline(): ?DateTimeImmutable;
    public function isIndividual(): bool;
    public function isLimited(): bool;
    public function hasTimeLimitFromStart(): bool;
    public function isStarted(): bool;
    public function isNowBeforeAllowedTime(): bool;
    public function isNowAfterAllowedTime(): bool;
    public function isNowInAllowedTime(): bool;
    public function isEndBeforeStart(): bool;
    public function isTimeLimitTooLong(): bool;
    public function format(SystemFormat $system_format): string;
    public function validate(?ValidationErrorStore $store = null) : bool;
}
