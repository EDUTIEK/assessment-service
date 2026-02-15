<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\WorkingTime\IndividualWorkingTime;

abstract class Writer implements AssessmentEntity, IndividualWorkingTime
{
    abstract public function getId(): int;
    abstract public function setId(int $id): self;
    abstract public function getUserId(): int;
    abstract public function setUserId(int $user_id): self;
    abstract public function getPseudonym(): string;
    abstract public function setPseudonym(string $pseudonym): self;
    abstract public function getAssId(): int;
    abstract public function setAssId(int $ass_id): self;
    abstract public function getEarliestStart(): ?DateTimeImmutable;
    abstract public function setEarliestStart(?DateTimeImmutable $earliest_start): self;
    abstract public function getLatestEnd(): ?DateTimeImmutable;
    abstract public function setLatestEnd(?DateTimeImmutable $latest_end): self;
    abstract public function getTimeLimitMinutes(): ?int;
    abstract public function setTimeLimitMinutes(?int $time_limit_minutes): self;
    abstract public function getWorkingStart(): ?DateTimeImmutable;
    abstract public function setWorkingStart(?DateTimeImmutable $working_start): self;
    abstract public function getFinalPoints(): ?float;
    abstract public function setFinalPoints(?float $final_points): self;
    /** @depracated  */
    abstract public function getFinalGradeLevelId(): ?int;
    /** @depracated  */
    abstract public function setFinalGradeLevelId(?int $final_grade_level_id): self;
    abstract public function getWritingAuthorized(): ?DateTimeImmutable;
    abstract public function setWritingAuthorized(?DateTimeImmutable $writing_authorized): self;
    abstract public function getWritingAuthorizedBy(): ?int;
    abstract public function setWritingAuthorizedBy(?int $writing_authorized_by): self;
    abstract public function getWritingExcluded(): ?DateTimeImmutable;
    abstract public function setWritingExcluded(?DateTimeImmutable $writing_excluded): self;
    abstract public function getWritingExcludedBy(): ?int;
    abstract public function setWritingExcludedBy(?int $writing_excluded_by): self;
    abstract public function getCorrectionStatus(): CorrectionStatus;
    abstract public function setCorrectionStatus(CorrectionStatus $status): self;
    abstract public function getCorrectionStatusChanged(): ?DateTimeImmutable;
    abstract public function setCorrectionStatusChanged(DateTimeImmutable $correction_status_changed): self;
    abstract public function getCorrectionStatusChangedBy(): ?int;
    abstract public function setCorrectionStatusChangedBy(?int $correction_status_changed_by): self;
    abstract public function getStitchComment(): ?string;
    abstract public function setStitchComment(?string $stitch_comment): self;
    abstract public function getLocation(): ?int;
    abstract public function setLocation(?int $location): self;
    abstract public function getReviewNotification(): int;
    abstract public function setReviewNotification(int $review_notification): self;
    abstract public function getFinalizedFromStatus(): ?CorrectionStatus;
    abstract public function setFinalizedFromStatus(?CorrectionStatus $finalized_from_status): self;

    public function getWritingStatus(): WritingStatus
    {
        $status = WritingStatus::NOT_STARTED;
        if ($this->getWritingExcluded() !== null) {
            $status = WritingStatus::EXCLUDED;
        } elseif ($this->getWritingAuthorized() !== null) {
            $status = WritingStatus::AUTHORIZED;
        } elseif ($this->getWorkingStart() !== null) {
            $status = WritingStatus::STARTED;
        }
        return $status;
    }

    /**
     * @return CombinedStatus
     */
    public function getCombinedStatus(): CombinedStatus
    {
        $writing_status = $this->getWritingStatus();
        if ($writing_status !== WritingStatus::AUTHORIZED) {
            return CombinedStatus::from($writing_status->value);
        }

        return match ($this->getCorrectionStatus()) {
            CorrectionStatus::OPEN => CombinedStatus::OPEN,
            CorrectionStatus::APPROXIMATION => CombinedStatus::APPROXIMATION,
            CorrectionStatus::CONSULTING => CombinedStatus::CONSULTING,
            CorrectionStatus::STITCH => CombinedStatus::STITCH_NEEDED,
            CorrectionStatus::FINALIZED => CombinedStatus::FINALIZED,
        };
    }

    public function isAuthorized(): bool
    {
        return $this->getWritingAuthorized() !== null;
    }

    public function canGetAuthorized(): bool
    {
        return $this->getWorkingStart() !== null
            && $this->getWritingAuthorized() === null;
    }

    public function canGetUnauthorized(): bool
    {
        return $this->getWorkingStart() !== null
            && $this->getWritingAuthorized() !== null;
    }

    public function hasChangedTimeLimit(): bool
    {
        return $this->getEarliestStart() !== null
            || $this->getLatestEnd() !== null
            || $this->getTimeLimitMinutes() !== null;
    }

    public function canChangeWorkingTime(): bool
    {
        return $this->getWritingAuthorized() === null && $this->getCorrectionFinalized() === null;
    }

    public function isExcluded(): bool
    {
        return $this->getWritingExcluded() !== null;
    }

    /**
     * @see WriterRepo::correctableIds
     */
    public function canBeCorrected(): bool
    {
        return $this->isAuthorized() && !$this->isExcluded();
    }

    public function canGetExcluded(): bool
    {
        return $this->getWritingExcluded() === null;
    }

    public function canGetRepealed(): bool
    {
        return $this->getWritingExcluded() !== null;
    }

    public function canGetSight(): bool
    {
        return $this->getWorkingStart() !== null;
    }

    public function canDownloadWrittenPdf(): bool
    {
        return !empty($this->getWorkingStart());
    }

    public function getStitchNeeded(): bool
    {
        return $this->getCorrectionStatus() === CorrectionStatus::STITCH;
    }

    public function getCorrectionFinalized(): ?DateTimeImmutable
    {
        if ($this->getCorrectionStatus() === CorrectionStatus::FINALIZED) {
            return $this->getCorrectionStatusChanged();
        }
        return null;
    }

    public function getCorrectionFinalizedBy(): ?int
    {
        if ($this->getCorrectionStatus() === CorrectionStatus::FINALIZED) {
            return $this->getCorrectionStatusChangedBy();
        }
        return null;
    }

    public function isCorrectionFinalized(): bool
    {
        return $this->getCorrectionStatus() === CorrectionStatus::FINALIZED;
    }

    public function canFinalizedUnsubmitted(): bool
    {
        return !$this->isAuthorized() && !$this->isCorrectionFinalized();
    }

    public function isUnsubmitted(): bool
    {
        return  !$this->isAuthorized() && $this->isCorrectionFinalized();
    }
}
