<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

use DateTimeImmutable;

abstract class CorrectorSummary implements TaskEntity
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getTaskId(): int;
    abstract public function setTaskId(int $task_id): self;
    abstract public function getWriterId(): int;
    abstract public function setWriterId(int $writer_id): self;
    abstract public function getCorrectorId(): int;
    abstract public function setCorrectorId(int $corrector_id): self;
    abstract public function getSummaryText(): ?string;
    abstract public function setSummaryText(?string $summary_text): self;
    abstract public function getPoints(): ?float;
    abstract public function setPoints(?float $points): self;
    abstract public function getLastChange(): ?DateTimeImmutable;
    abstract public function setLastChange(?DateTimeImmutable $last_change): self;
    abstract public function getCorrectionAuthorized(): ?DateTimeImmutable;
    abstract public function setCorrectionAuthorized(?DateTimeImmutable $corection_authorized): self;
    abstract public function getCorrectionAuthorizedBy(): ?int;
    abstract public function setCorrectionAuthorizedBy(?int $correction_authorized_by): self;

    public function getGradingStatus() : GradingStatus
    {
        if(empty($this->getLastChange())) {
            return GradingStatus::NOT_STARTED;
        }


        if (empty($this->getCorrectionAuthorized())) {
            return GradingStatus::OPEN;
        }

        return GradingStatus::AUTHORIZED;
    }

    public function isAuthorized() : bool
    {
        return $this->getCorrectionAuthorized() !== null;
    }
}
