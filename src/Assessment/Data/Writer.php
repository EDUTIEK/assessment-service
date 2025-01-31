<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class Writer implements AssessmentEntity
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
    abstract public function getFinalGradeLevelId(): ?int;
    abstract public function setFinalGradeLevelId(?int $final_grade_level_id): self;
    abstract public function getWritingAuthorized(): ?DateTimeImmutable;
    abstract public function setWritingAuthorized(?DateTimeImmutable $writing_authorized): self;
    abstract public function getWritingAuthorizedBy(): ?int;
    abstract public function setWritingAuthorizedBy(?int $writing_authorized_by): self;
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
}
