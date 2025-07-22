<?php

namespace Edutiek\AssessmentService\Assessment\WorkingTime;

use DateTimeImmutable;

interface IndividualWorkingTime
{
    public function getEarliestStart(): ?DateTimeImmutable;
    public function setEarliestStart(?DateTimeImmutable $earliest_start): self;
    public function getLatestEnd(): ?DateTimeImmutable;
    public function setLatestEnd(?DateTimeImmutable $latest_end): self;
    public function getTimeLimitMinutes(): ?int;
    public function setTimeLimitMinutes(?int $time_limit_minutes): self;
    public function getWorkingStart(): ?DateTimeImmutable;
    public function setWorkingStart(?DateTimeImmutable $working_start): self;
}