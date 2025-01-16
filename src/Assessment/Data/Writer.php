<?php


declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class Writer implements AssessmentEntity
{
    public abstract function getId(): int;
    public abstract function setId(int $id): void;
    public abstract function getUserId(): int;
    public abstract function setUserId(int $user_id): void;
    public abstract function getPseudonym(): string;
    public abstract function setPseudonym(string $pseudonym): void;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): void;
    public abstract function getEarliestStart(): ?DateTimeImmutable;
    public abstract function setEarliestStart(?DateTimeImmutable $earliest_start): void;
    public abstract function getLatestEnd(): ?DateTimeImmutable;
    public abstract function setLatestEnd(?DateTimeImmutable $latest_end): void;
    public abstract function getTimeLimitMinutes(): ?int;
    public abstract function setTimeLimitMinutes(?int $time_limit_minutes): void;
    public abstract function getWorkingStart(): ?DateTimeImmutable;
    public abstract function setWorkingStart(?DateTimeImmutable $working_start): void;
    public abstract function getFinalPoints(): ?float;
    public abstract function setFinalPoints(?float $final_points): void;
    public abstract function getFinalGradeLevelId(): ?int;
    public abstract function setFinalGradeLevelId(?int $final_grade_level_id): void;
    public abstract function getWritingAuthorized(): ?DateTimeImmutable;
    public abstract function setWritingAuthorized(?DateTimeImmutable $writing_authorized): void;
    public abstract function getWritingAuthorizedBy(): ?int;
    public abstract function setWritingAuthorizedBy(?int $writing_authorized_by): void;
    public abstract function getCorrectionFinalizedBy(): ?int;
    public abstract function setCorrectionFinalizedBy(?int $correction_finalized_by): void;
    public abstract function getWritingExcluded(): ?DateTimeImmutable;
    public abstract function setWritingExcluded(?DateTimeImmutable $writing_excluded): void;
    public abstract function getWritingExcludedBy(): ?int;
    public abstract function setWritingExcludedBy(?int $writing_excluded_by): void;
    public abstract function getStitchComment(): ?string;
    public abstract function setStitchComment(?string $stitch_comment): void;
    public abstract function getLocation(): ?int;
    public abstract function setLocation(?int $location): void;
    public abstract function getReviewNotification(): int;
    public abstract function setReviewNotification(int $review_notification): void;
}
