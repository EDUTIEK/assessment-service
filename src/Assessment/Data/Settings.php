<?php

declare(strict_types=1);

namespace Edutiek\AssessmentService\Assessment\Data;

use DateTimeImmutable;

abstract class Settings implements AssessmentEntity
{
    public abstract function getOnline(): int;
    public abstract function setOnline(int $online): self;
    public abstract function getParticipationType(): string;
    public abstract function setParticipationType(string $participation_type): self;
    public abstract function getAssId(): int;
    public abstract function setAssId(int $ass_id): self;
    public abstract function getDescription(): ?string;
    public abstract function setDescription(?string $description): self;
    public abstract function getClosingMessage(): ?string;
    public abstract function setClosingMessage(?string $closing_message): self;
    public abstract function getWritingStart(): ?DateTimeImmutable;
    public abstract function setWritingStart(?DateTimeImmutable $writing_start): self;
    public abstract function getWritingEnd(): ?DateTimeImmutable;
    public abstract function setWritingEnd(?DateTimeImmutable $writing_end): self;
    public abstract function getWritingLimitMinutes(): ?int;
    public abstract function setWritingLimitMinutes(?int $writing_limit_minutes): self;
    public abstract function getCorrectionStart(): ?DateTimeImmutable;
    public abstract function setCorrectionStart(?DateTimeImmutable $correction_start): self;
    public abstract function getCorrectionEnd(): ?DateTimeImmutable;
    public abstract function setCorrectionEnd(?DateTimeImmutable $correction_end): self;
    public abstract function getReviewStart(): ?DateTimeImmutable;
    public abstract function setReviewStart(?DateTimeImmutable $review_start): self;
    public abstract function getReviewEnd(): ?DateTimeImmutable;
    public abstract function setReviewEnd(?DateTimeImmutable $review_end): self;
    public abstract function getKeepAvailable(): int;
    public abstract function setKeepAvailable(int $keep_available): self;
    public abstract function getSolutionAvailableDate(): ?DateTimeImmutable;
    public abstract function setSolutionAvailableDate(?DateTimeImmutable $solution_available_date): self;
    public abstract function getResultAvailableType(): string;
    public abstract function setResultAvailableType(string $result_available_type): self;
    public abstract function getResultAvailableDate(): ?DateTimeImmutable;
    public abstract function setResultAvailableDate(?DateTimeImmutable $result_available_date): self;
    public abstract function getSolutionAvailable(): int;
    public abstract function setSolutionAvailable(int $solution_available): self;
    public abstract function getReviewEnabled(): int;
    public abstract function setReviewEnabled(int $review_enabled): self;
    public abstract function getReviewNotification(): int;
    public abstract function setReviewNotification(int $review_notification): self;
    public abstract function getReviewNotifText(): ?string;
    public abstract function setReviewNotifText(?string $review_notif_text): self;
    public abstract function getStatisticsAvailable(): int;
    public abstract function setStatisticsAvailable(int $statistics_available): self;
}
