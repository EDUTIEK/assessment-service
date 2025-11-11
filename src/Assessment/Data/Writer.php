<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;
use Edutiek\AssessmentService\Assessment\WorkingTime\ValidationErrorStore;
use Edutiek\AssessmentService\Assessment\WorkingTime\ValidationError;
use Edutiek\AssessmentService\Assessment\WorkingTime\IndividualWorkingTime;

abstract class Writer implements AssessmentEntity, ValidationErrorStore, IndividualWorkingTime
{
    /** @var ValidationError[] */
    private $validation_errors = [];
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
    abstract public function getFinalGradeLevelId(): ?int;
    abstract public function setFinalGradeLevelId(?int $final_grade_level_id): self;
    abstract public function getWritingAuthorized(): ?DateTimeImmutable;
    abstract public function setWritingAuthorized(?DateTimeImmutable $writing_authorized): self;
    abstract public function getWritingAuthorizedBy(): ?int;
    abstract public function setWritingAuthorizedBy(?int $writing_authorized_by): self;
    abstract public function getCorrectionFinalized(): ?DateTimeImmutable;
    abstract public function setCorrectionFinalized(?DateTimeImmutable $correction_finalized): self;
    abstract public function getCorrectionFinalizedBy(): ?int;
    abstract public function setCorrectionFinalizedBy(?int $correction_finalized_by): self;
    abstract public function getWritingExcluded(): ?DateTimeImmutable;
    abstract public function setWritingExcluded(?DateTimeImmutable $writing_excluded): self;
    abstract public function getWritingExcludedBy(): ?int;
    abstract public function setWritingExcludedBy(?int $writing_excluded_by): self;
    abstract public function getStitchComment(): ?string;
    abstract public function setStitchComment(?string $stitch_comment): self;
    abstract public function getLocation(): ?int;
    abstract public function setLocation(?int $location): self;
    abstract public function getReviewNotification(): int;
    abstract public function setReviewNotification(int $review_notification): self;
    abstract public function setStitchNeeded(bool $stitch_needed): self;
    abstract public function getStitchNeeded(): bool;

    public function getWritingStatus(): WritingStatus
    {
        if($this->getWritingExcluded() !== null){
            return WritingStatus::EXCLUDED;
        }
        if($this->getWritingAuthorized() !== null) {
            return WritingStatus::AUTHORIZED;
        }
        if($this->getWorkingStart() !== null) {
            return WritingStatus::STARTED;
        }
        return WritingStatus::NOT_STARTED;
    }

    public function isAuthorized() : bool
    {
        return $this->getWritingAuthorized() !== null;
    }

    public function canGetAuthorized() : bool
    {
        return $this->getWorkingStart() !== null
            && $this->getWritingAuthorized() === null;
    }

    public function canGetUnauthorized() : bool
    {
        return $this->getWorkingStart() !== null
            && $this->getWritingAuthorized() !== null;
    }

    public function hasChangedTimeLimit() : bool
    {
        return $this->getEarliestStart() !== null
            || $this->getLatestEnd() !== null
            || !empty($this->getTimeLimitMinutes());
    }

    public function canChangeWorkingTime() : bool
    {
        return $this->getWritingAuthorized() === null && $this->getCorrectionFinalized() === null;
    }

    public function isExcluded() : bool
    {
        return $this->getWritingExcluded() !== null;
    }

    public function canGetExcluded() : bool
    {
        return $this->getWritingExcluded() === null;
    }

    public function canGetRepealed() : bool
    {
        return $this->getWritingExcluded() !== null;
    }

    public function canGetSight() : bool
    {
        return $this->getWorkingStart() !== null;
    }

    public function isCorrectionFinalized() : bool
    {
        return $this->getCorrectionFinalized() !== null;
    }

    public function addValidationError(ValidationError $error) : void
    {
        $this->validation_errors[] = $error;
    }

    public function getValidationErrors(): array
    {
        return $this->validation_errors;
    }

    public function canDownloadWrittenPdf(): bool
    {
        return !empty($this->getWorkingStart());
    }
}
