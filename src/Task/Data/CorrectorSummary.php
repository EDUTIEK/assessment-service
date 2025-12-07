<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Task\Data;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\TaskInterfaces\GradingStatus;

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
    abstract public function getSummaryPdf(): ?string;
    abstract public function setSummaryPdf(?string $summary_pdf): self;
    abstract public function getPoints(): ?float;
    abstract public function setPoints(?float $points): self;
    abstract public function getLastChange(): ?DateTimeImmutable;
    abstract public function setLastChange(?DateTimeImmutable $last_change): self;
    abstract public function getCorrectionAuthorized(): ?DateTimeImmutable;
    abstract public function setCorrectionAuthorized(?DateTimeImmutable $corection_authorized): self;
    abstract public function getCorrectionAuthorizedBy(): ?int;
    abstract public function setCorrectionAuthorizedBy(?int $correction_authorized_by): self;
    abstract public function getPreGraded(): ?DateTimeImmutable;
    abstract public function setPreGraded(?DateTimeImmutable $pre_graded): self;
    abstract public function getRevised(): ?DateTimeImmutable;
    abstract public function setRevised(?DateTimeImmutable $revised): self;
    abstract public function getRevisionText(): ?string;
    abstract public function setRevisionText(?string $revision_text): self;
    abstract public function getRevisionPoints(): ?float;
    abstract public function setRevisionPoints(?float $revision_points): self;
    abstract public function getRequireOtherRevision(): bool;
    abstract public function setRequireOtherRevision(bool $require_other_revision): self;

    /**
     * Get the effective points from a revision or correction
     */
    public function getEffectivePoints(): ?float
    {
        return $this->isRevised() ? $this->getRevisionPoints() : $this->getPoints();
    }

    /**
     * Get the full Grading Status
     * @return GradingStatus
     */
    public function getGradingStatus(): GradingStatus
    {
        if (!empty($this->getRevised())) {
            return GradingStatus::REVISED;
        }
        if (!empty($this->getCorrectionAuthorized())) {
            return GradingStatus::AUTHORIZED;
        }
        if (!empty($this->getPreGraded())) {
            return GradingStatus::PRE_GRADED;
        }
        if (!empty($this->getLastChange())) {
            return GradingStatus::OPEN;
        }
        return GradingStatus::NOT_STARTED;

        if (empty($this->getLastChange())) {
            return GradingStatus::NOT_STARTED;
        }
    }

    /**
     * Get a reduced stading status if everything before authorization should not be public
     * @return GradingStatus
     */
    public function getGradingStatusLight(): GradingStatus
    {
        if (!empty($this->getRevised())) {
            return GradingStatus::REVISED;
        }
        if (!empty($this->getCorrectionAuthorized())) {
            return GradingStatus::AUTHORIZED;
        }
        return GradingStatus::NOT_STARTED;
    }


    public function setGradingStatus(GradingStatus $status, int $user_id): self
    {
        switch ($status) {
            case GradingStatus::PRE_GRADED:
                $this->setPreGraded(new DateTimeImmutable());
                break;
            case GradingStatus::AUTHORIZED:
                $this->setCorrectionAuthorized(new DateTimeImmutable());
                $this->setCorrectionAuthorizedBy($user_id);
                break;
            case GradingStatus::REVISED:
                $this->setRevised(new DateTimeImmutable());
                break;
            default:
                $this->setPreGraded(null);
                $this->setCorrectionAuthorized(null);
                $this->setCorrectionAuthorizedBy(null);
                $this->setRevised(null);
                $this->setPreGraded(null);
        }
        return $this;
    }

    /**
     * The summary has been authorized
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return $this->getCorrectionAuthorized() !== null;
    }

    public function isRevised(): bool
    {
        return $this->getRevised() !== null;
    }
}
